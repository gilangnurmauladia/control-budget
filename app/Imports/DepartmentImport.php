<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Location;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class DepartmentImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        if (empty($row['kode_dept']) || empty($row['nama_departemen'])) return null;

        $dept = Department::firstOrCreate(
            ['dept_code' => strtoupper(trim($row['kode_dept']))],
            ['dept_name' => trim($row['nama_departemen'])]
        );

        // Auto-create GENERAL location if new
        if ($dept->wasRecentlyCreated) {
            Location::firstOrCreate(
                ['location_code' => $dept->dept_code . '-GEN'],
                [
                    'department_id' => $dept->id,
                    'location_name' => 'GENERAL - ' . $dept->dept_name,
                    'is_default'    => true,
                    'status'        => 'active',
                ]
            );
        }

        return null; // we handle saving manually above
    }
}
