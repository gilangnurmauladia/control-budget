<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ItemImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        if (empty($row['kode_item']) || empty($row['nama_item'])) return null;

        $category = ItemCategory::firstOrCreate(['category_name' => trim($row['kategori'] ?? 'Consumable')]);

        $item = Item::updateOrCreate(
            ['item_code' => strtoupper(trim($row['kode_item']))],
            [
                'category_id' => $category->id,
                'item_name'   => trim($row['nama_item']),
                'unit'        => trim($row['satuan'] ?? 'Pcs'),
                'unit_price'  => (float) str_replace(',', '', $row['harga_satuan'] ?? 0),
                'status'      => 'active',
            ]
        );

        // Check various possible heading keys for department
        $deptVal = $row['departemen'] 
            ?? $row['department'] 
            ?? $row['departemen_kode_dipisah_koma'] 
            ?? $row['departemen_kode'] 
            ?? null;

        if (!empty($deptVal)) {
            $deptCodes = array_map('trim', explode(',', (string)$deptVal));
            
            $deptIds = Department::where(function($q) use ($deptCodes) {
                foreach ($deptCodes as $code) {
                    $q->orWhere('dept_code', 'LIKE', "%{$code}%")
                      ->orWhere('dept_name', 'LIKE', "%{$code}%");
                }
            })->pluck('id')->toArray();

            if (!empty($deptIds)) {
                $item->departments()->sync($deptIds);
            }
        }

        return null;
    }
}
