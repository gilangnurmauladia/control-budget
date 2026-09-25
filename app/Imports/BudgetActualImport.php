<?php

namespace App\Imports;

use App\Models\BudgetActual;
use App\Models\Department;
use App\Models\Item;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class BudgetActualImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        if (empty($row['kode_dept']) || empty($row['kode_item']) || empty($row['qty_actual'])) return null;

        $dept = Department::where('dept_code', strtoupper(trim($row['kode_dept'])))->first();
        $item = Item::where('item_code', strtoupper(trim($row['kode_item'])))->first();

        if (!$dept || !$item) return null;

        $date = Carbon::parse($row['tanggal_transaksi'] ?? now())->toDateString();

        return new BudgetActual([
            'transaction_date'    => $date,
            'department_id'       => $dept->id,
            'location_id'         => null,
            'item_id'             => $item->id,
            'actual_qty'          => (float) $row['qty_actual'],
            'unit_price_snapshot' => $item->unit_price,
            'notes'               => $row['catatan'] ?? null,
            'created_by'          => auth()->id() ?? 1,
        ]);
    }
}
