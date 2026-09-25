<?php

namespace App\Http\Controllers;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanRevision;
use App\Models\BudgetActual;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetPlanExport;
use App\Imports\BudgetPlanImport;
use Barryvdh\DomPDF\Facade\Pdf;

class BudgetPlanController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');

        $query = BudgetPlan::with(['department', 'item', 'item.category', 'location'])
            ->where('period', $period);

        // Scope by department for PIC or Admin Gudang
        if ($user->hasRole(['PIC Departemen', 'Admin Gudang']) && $user->department_id) {
            $query->where('department_id', $user->department_id);
            $deptId = $user->department_id;
        } elseif ($deptId) {
            $query->where('department_id', $deptId);
        }

        $plans = $query->orderBy('department_id')->orderBy('item_id')->get();

        // Enrich with actual qty
        $plans = $plans->map(function ($plan) use ($period) {
            $actualQty = BudgetActual::where('department_id', $plan->department_id)
                ->where('item_id', $plan->item_id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                ->sum('actual_qty');
            $plan->actual_qty = $actualQty;
            $plan->actual_nominal = $actualQty * $plan->unit_price_snapshot;
            $plan->plan_nominal = $plan->plan_qty * $plan->unit_price_snapshot;
            $plan->pct = $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0;
            $plan->is_over = $plan->pct > 100;
            return $plan;
        });

        $departments = Department::orderBy('dept_name')->get();

        if ($deptId) {
            $items = Item::whereHas('departments', fn($q) => $q->where('master_departments.id', $deptId))
                ->where('status', 'active')
                ->orderBy('item_name')
                ->get();
        } else {
            $items = Item::where('status', 'active')->orderBy('item_name')->get();
        }

        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $periods[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        return view('budget.plans.index', compact(
            'plans', 'period', 'periods', 'departments', 'items', 'locations', 'deptId'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'period'        => 'required|string|regex:/^\d{4}-\d{2}$/',
            'department_id' => 'required|exists:master_departments,id',
            'item_id'       => 'required|exists:master_items,id',
            'location_id'   => 'nullable|exists:master_locations,id',
            'plan_qty'      => 'required|numeric|min:0.001',
        ]);

        // Check if plan already exists
        $existing = BudgetPlan::where('period', $validated['period'])
            ->where('department_id', $validated['department_id'])
            ->where('item_id', $validated['item_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Plan untuk item ini sudah ada pada periode tersebut. Gunakan fitur Revisi Plan.');
        }

        $item = Item::find($validated['item_id']);
        BudgetPlan::create([
            ...$validated,
            'unit_price_snapshot' => $item->unit_price,
            'current_version'     => 1.0,
            'created_by'          => $user->id,
        ]);

        return redirect()->route('budget.plans.index', ['period' => $validated['period'], 'department_id' => $validated['department_id']])
            ->with('success', 'Budget plan berhasil dibuat.');
    }

    public function revise(Request $request, BudgetPlan $plan)
    {
        $validated = $request->validate([
            'new_plan_qty'    => 'required|numeric|min:0.001',
            'reason_category' => 'required|string|max:100',
            'reason_detail'   => 'required|string|min:10',
        ]);

        DB::transaction(function () use ($plan, $validated) {
            $oldQty         = $plan->plan_qty;
            $oldVersion     = $plan->current_version;
            $newVersion     = round($oldVersion + 0.1, 1);

            // Log revision
            BudgetPlanRevision::create([
                'budget_plan_id'  => $plan->id,
                'revision_date'   => now(),
                'old_plan_qty'    => $oldQty,
                'new_plan_qty'    => $validated['new_plan_qty'],
                'version_from'    => $oldVersion,
                'version_to'      => $newVersion,
                'reason_category' => $validated['reason_category'],
                'reason_detail'   => $validated['reason_detail'],
                'revised_by'      => auth()->id(),
            ]);

            // Update plan
            $plan->update([
                'plan_qty'        => $validated['new_plan_qty'],
                'current_version' => $newVersion,
            ]);
        });

        return redirect()->route('budget.plans.index', ['period' => $plan->period, 'department_id' => $plan->department_id])
            ->with('success', 'Revisi plan berhasil disimpan. Versi ' . $plan->fresh()->current_version);
    }

    public function revisionHistory(BudgetPlan $plan)
    {
        $revisions = $plan->revisions()->with('reviser')->orderByDesc('revision_date')->get();
        return view('budget.plans.revisions', compact('plan', 'revisions'));
    }

    public function export(Request $request)
    {
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');
        return Excel::download(new BudgetPlanExport($period, $deptId ? (int)$deptId : null), 'budget_plan_' . $period . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');

        $query = BudgetPlan::with(['department', 'item', 'item.category'])
            ->where('period', $period);

        if ($deptId) {
            $query->where('department_id', $deptId);
        }

        $plans = $query->get()->map(function ($plan) use ($period) {
            $actualQty = BudgetActual::where('department_id', $plan->department_id)
                ->where('item_id', $plan->item_id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                ->sum('actual_qty');
            $plan->actual_qty   = $actualQty;
            $plan->pct          = $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0;
            return $plan;
        });

        $pdf = Pdf::loadView('budget.plans.pdf', compact('plans', 'period'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('budget_plan_' . $period . '.pdf');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv|max:5120']);
        
        $import = new BudgetPlanImport;
        Excel::import($import, $request->file('file'));
        
        if ($import->errors()->isNotEmpty()) {
            return redirect()->route('budget.plans.index')
                ->with('error', 'Beberapa data gagal diimport. Silakan periksa file Anda.');
        }

        return redirect()->route('budget.plans.index')
            ->with('success', 'Import budget plan berhasil.');
    }
}
