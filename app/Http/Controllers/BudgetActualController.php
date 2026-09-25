<?php

namespace App\Http\Controllers;

use App\Models\BudgetActual;
use App\Models\BudgetPlan;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetActualExport;
use App\Imports\BudgetActualImport;
use Barryvdh\DomPDF\Facade\Pdf;

class BudgetActualController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $deptId = $request->get('department_id');
        $user   = auth()->user();

        $query = BudgetActual::with(['department', 'item', 'item.category', 'location', 'creator'])
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->orderByDesc('transaction_date');

        if ($user->hasRole(['PIC Departemen', 'Admin Gudang']) && $user->department_id) {
            $query->where('department_id', $user->department_id);
        } elseif ($deptId) {
            $query->where('department_id', $deptId);
        }

        $actuals = $query->get();
        $departments = Department::orderBy('dept_name')->get();
        $items = Item::where('status', 'active')->orderBy('item_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $periods[] = Carbon::now()->subMonths($i)->format('Y-m');
        }

        return view('budget.actuals.index', compact(
            'actuals', 'period', 'periods', 'departments', 'items', 'locations', 'deptId'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'department_id'    => 'required|exists:master_departments,id',
            'item_id'          => 'required|exists:master_items,id',
            'location_id'      => 'nullable|exists:master_locations,id',
            'actual_qty'       => 'required|numeric|min:0.001',
            'notes'            => 'nullable|string|max:500',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $actual = BudgetActual::create([
            ...$validated,
            'unit_price_snapshot' => $item->unit_price,
            'created_by'          => $user->id,
        ]);

        return redirect()->route('budget.actuals.index')
            ->with('success', 'Transaksi actual berhasil dicatat.');
    }

    public function edit(BudgetActual $actual)
    {
        $departments = Department::orderBy('dept_name')->get();
        $items = Item::where('status', 'active')->orderBy('item_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();
        return view('budget.actuals.edit', compact('actual', 'departments', 'items', 'locations'));
    }

    public function update(Request $request, BudgetActual $actual)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'department_id'    => 'required|exists:master_departments,id',
            'item_id'          => 'required|exists:master_items,id',
            'location_id'      => 'nullable|exists:master_locations,id',
            'actual_qty'       => 'required|numeric|min:0.001',
            'notes'            => 'nullable|string|max:500',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $actual->update([
            ...$validated,
            'unit_price_snapshot' => $item->unit_price,
        ]);

        return redirect()->route('budget.actuals.index')
            ->with('success', 'Transaksi actual berhasil diperbarui.');
    }

    public function destroy(BudgetActual $actual)
    {
        $actual->delete();
        return redirect()->route('budget.actuals.index')
            ->with('success', 'Transaksi actual berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        return Excel::download(new BudgetActualExport($period), 'budget_actual_' . $period . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $period  = $request->get('period', Carbon::now()->format('Y-m'));
        $actuals = BudgetActual::with(['department', 'item', 'creator'])
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->orderBy('transaction_date')
            ->get();

        $pdf = Pdf::loadView('budget.actuals.pdf', compact('actuals', 'period'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('budget_actual_' . $period . '.pdf');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv|max:5120']);
        Excel::import(new BudgetActualImport, $request->file('file'));
        return redirect()->route('budget.actuals.index')
            ->with('success', 'Import actual berhasil.');
    }
}
