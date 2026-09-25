<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepartmentExport;
use App\Imports\DepartmentImport;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('locations')->orderBy('dept_code')->get();
        return view('master.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('master.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dept_code' => 'required|string|max:20|unique:master_departments,dept_code',
            'dept_name' => 'required|string|max:100',
        ]);

        $dept = Department::create($validated);

        // Auto-create GENERAL location
        Location::create([
            'department_id' => $dept->id,
            'location_code' => $dept->dept_code . '-GEN',
            'location_name' => 'GENERAL - ' . $dept->dept_name,
            'is_default' => true,
            'status' => 'active',
        ]);

        return redirect()->route('master.departments.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        return view('master.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'dept_code' => 'required|string|max:20|unique:master_departments,dept_code,' . $department->id,
            'dept_name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);
        return redirect()->route('master.departments.index')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0 || $department->budgetPlans()->count() > 0) {
            return back()->with('error', 'Departemen tidak bisa dihapus karena memiliki data terkait.');
        }
        $department->delete();
        return redirect()->route('master.departments.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new DepartmentExport, 'departments_' . now()->format('Ymd') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv|max:2048']);
        Excel::import(new DepartmentImport, $request->file('file'));
        return redirect()->route('master.departments.index')
            ->with('success', 'Data departemen berhasil diimport.');
    }
}
