<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Department::orderBy('dept_code')->get();
    }

    public function headings(): array
    {
        return ['Kode Dept', 'Nama Departemen', 'Status'];
    }

    public function map($dept): array
    {
        return [$dept->dept_code, $dept->dept_name, $dept->is_active ? 'Aktif' : 'Tidak Aktif'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
