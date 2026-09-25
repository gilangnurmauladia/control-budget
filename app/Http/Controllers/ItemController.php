<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Department;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemExport;
use App\Imports\ItemImport;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $deptId = $request->get('department_id');

        $query = Item::with(['category', 'departments'])->orderBy('item_code');

        // Scope by department for PIC or Admin Gudang
        if ($user->hasRole(['PIC Departemen', 'Admin Gudang']) && $user->department_id) {
            $query->whereHas('departments', function ($q) use ($user) {
                $q->where('master_departments.id', $user->department_id);
            });
            $deptId = $user->department_id;
        } elseif ($deptId) {
            $query->whereHas('departments', function ($q) use ($deptId) {
                $q->where('master_departments.id', $deptId);
            });
        }

        $items = $query->get();
        $categories = ItemCategory::orderBy('category_name')->get();
        $departments = Department::orderBy('dept_name')->get();

        return view('master.items.index', compact('items', 'categories', 'departments', 'deptId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'      => 'required|exists:master_item_categories,id',
            'item_code'        => 'required|string|max:30|unique:master_items,item_code',
            'item_name'        => 'required|string|max:200',
            'unit'             => 'required|string|max:30',
            'unit_price'       => 'required|numeric|min:0',
            'status'           => 'in:active,inactive',
            'department_ids'   => 'required|array|min:1',
            'department_ids.*' => 'exists:master_departments,id',
        ]);

        $item = Item::create([
            'category_id' => $validated['category_id'],
            'item_code'   => $validated['item_code'],
            'item_name'   => $validated['item_name'],
            'unit'        => $validated['unit'],
            'unit_price'  => $validated['unit_price'],
            'status'      => $validated['status'] ?? 'active',
        ]);

        $item->departments()->sync($validated['department_ids']);

        return redirect()->route('master.items.index')
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'category_id'      => 'required|exists:master_item_categories,id',
            'item_code'        => 'required|string|max:30|unique:master_items,item_code,' . $item->id,
            'item_name'        => 'required|string|max:200',
            'unit'             => 'required|string|max:30',
            'unit_price'       => 'required|numeric|min:0',
            'status'           => 'in:active,inactive',
            'department_ids'   => 'required|array|min:1',
            'department_ids.*' => 'exists:master_departments,id',
        ]);

        $item->update([
            'category_id' => $validated['category_id'],
            'item_code'   => $validated['item_code'],
            'item_name'   => $validated['item_name'],
            'unit'        => $validated['unit'],
            'unit_price'  => $validated['unit_price'],
            'status'      => $validated['status'] ?? 'active',
        ]);

        $item->departments()->sync($validated['department_ids']);

        return redirect()->route('master.items.index')
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->budgetPlans()->count() > 0 || $item->budgetActuals()->count() > 0) {
            return back()->with('error', 'Item tidak bisa dihapus karena memiliki transaksi terkait.');
        }
        $item->departments()->detach();
        $item->delete();
        return redirect()->route('master.items.index')
            ->with('success', 'Item berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new ItemExport, 'items_' . now()->format('Ymd') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv|max:2048']);
        Excel::import(new ItemImport, $request->file('file'));
        return redirect()->route('master.items.index')
            ->with('success', 'Data item berhasil diimport.');
    }
}
