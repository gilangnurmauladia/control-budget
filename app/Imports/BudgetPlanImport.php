<?php

namespace App\Imports;

use App\Models\BudgetPlan;
use App\Models\Department;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class BudgetPlanImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        // Cek apakah ada kode_dept atau departemen
        $deptKey = isset($row['kode_dept']) ? 'kode_dept' : (isset($row['departemen']) ? 'departemen' : null);

        if (empty($row['periode']) || !$deptKey || empty($row['kode_item']) || !isset($row['plan_qty'])) {
            \Log::warning('Import skipped: Missing data in row ' . json_encode($row));
            return null;
        }

        $deptValue = $row[$deptKey];
        
        $dept = null;
        if ($deptKey === 'kode_dept') {
            $dept = Department::where('dept_code', strtoupper(trim($deptValue)))->first();
        } else {
            $dept = Department::where('dept_name', trim($deptValue))->first();
        }

        $item = Item::where('item_code', strtoupper(trim($row['kode_item'])))->first();

        if (!$dept) {
            \Log::warning('Import skipped: Department not found: ' . $deptValue);
            return null;
        }
        if (!$item) {
            \Log::warning('Import skipped: Item not found: ' . $row['kode_item']);
            return null;
        }

        $period = trim($row['periode']);
        
        // Validasi periode YYYY-MM
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            \Log::warning('Import skipped: Invalid period format: ' . $period);
            return null;
        }

        \Log::info('Importing row: ' . json_encode([
            'period' => $period,
            'dept_id' => $dept->id,
            'item_id' => $item->id,
            'qty' => (float) $row['plan_qty']
        ]));

        BudgetPlan::updateOrCreate(
            [
                'period'        => $period,
                'department_id' => $dept->id,
                'item_id'       => $item->id,
            ],
            [
                'location_id'        => null,
                'plan_qty'           => (float) $row['plan_qty'],
                'unit_price_snapshot' => $item->unit_price,
                'current_version'    => 1.0,
                'created_by'         => auth()->id() ?? 1,
            ]
        );

        return null;
    }
}
