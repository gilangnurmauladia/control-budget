<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Item::with(['category', 'departments'])->orderBy('item_code')->get();
    }

    public function headings(): array
    {
        return ['Kode Item', 'Kategori', 'Nama Item', 'Satuan', 'Harga Satuan', 'Departemen (Kode dipisah koma)', 'Status'];
    }

    public function map($item): array
    {
        $deptCodes = $item->departments->pluck('dept_code')->implode(', ');

        return [
            $item->item_code,
            $item->category->category_name ?? '-',
            $item->item_name,
            $item->unit,
            $item->unit_price,
            $deptCodes,
            $item->status === 'active' ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
