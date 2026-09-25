<?php

namespace App\Http\Controllers;

use App\Models\BudgetPlan;
use App\Models\BudgetActual;
use App\Models\Department;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');

        $planQuery = BudgetPlan::with(['department', 'item', 'item.category'])
            ->where('period', $period);

        if ($user->hasRole(['PIC Departemen', 'Admin Gudang']) && $user->department_id) {
            $planQuery->where('department_id', $user->department_id);
            $deptId = $user->department_id;
        } elseif ($deptId) {
            $planQuery->where('department_id', $deptId);
        }

        $plans = $planQuery->orderBy('department_id')->orderBy('item_id')->get();

        // Enrich each plan dengan actual data
        $reportData = $plans->map(function ($plan) use ($period) {
            $actualQty = BudgetActual::where('department_id', $plan->department_id)
                ->where('item_id', $plan->item_id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                ->sum('actual_qty');

            return [
                'dept_name'      => $plan->department->dept_name,
                'item_code'      => $plan->item->item_code,
                'item_name'      => $plan->item->item_name,
                'category'       => $plan->item->category->category_name ?? '-',
                'unit'           => $plan->item->unit,
                'unit_price'     => $plan->unit_price_snapshot,
                'plan_qty'       => $plan->plan_qty,
                'plan_nominal'   => $plan->plan_qty * $plan->unit_price_snapshot,
                'actual_qty'     => $actualQty,
                'actual_nominal' => $actualQty * $plan->unit_price_snapshot,
                'remaining_qty'  => $plan->plan_qty - $actualQty,
                'remaining_nom'  => ($plan->plan_qty - $actualQty) * $plan->unit_price_snapshot,
                'pct'            => $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0,
                'status'         => ($plan->plan_qty > 0 && $actualQty > $plan->plan_qty) ? 'OVER' : 'OK',
                'version'        => $plan->current_version,
            ];
        });

        $departments = Department::orderBy('dept_name')->get();
        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $periods[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        return view('reports.index', compact('reportData', 'period', 'periods', 'departments', 'deptId'));
    }

    public function exportExcel(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');

        if ($user->hasRole('PIC Departemen') && $user->department_id) {
            $deptId = $user->department_id;
        }

        return Excel::download(new ReportExport($period, $deptId), 'laporan_budget_' . $period . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');

        if ($user->hasRole('PIC Departemen') && $user->department_id) {
            $deptId = $user->department_id;
        }

        $planQuery = BudgetPlan::with(['department', 'item', 'item.category'])
            ->where('period', $period);
        if ($deptId) $planQuery->where('department_id', $deptId);
        $plans = $planQuery->get();

        $reportData = $plans->map(function ($plan) use ($period) {
            $actualQty = BudgetActual::where('department_id', $plan->department_id)
                ->where('item_id', $plan->item_id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
                ->sum('actual_qty');
            return [
                'dept_name'      => $plan->department->dept_name,
                'item_name'      => $plan->item->item_name,
                'unit'           => $plan->item->unit,
                'unit_price'     => $plan->unit_price_snapshot,
                'plan_qty'       => $plan->plan_qty,
                'plan_nominal'   => $plan->plan_qty * $plan->unit_price_snapshot,
                'actual_qty'     => $actualQty,
                'actual_nominal' => $actualQty * $plan->unit_price_snapshot,
                'pct'            => $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0,
                'status'         => ($plan->plan_qty > 0 && $actualQty > $plan->plan_qty) ? 'OVER' : 'OK',
            ];
        });

        $deptName = $deptId ? Department::find($deptId)?->dept_name : 'Semua Departemen';
        $pdf = Pdf::loadView('reports.pdf', compact('reportData', 'period', 'deptName'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('laporan_budget_' . $period . '.pdf');
    }
}
