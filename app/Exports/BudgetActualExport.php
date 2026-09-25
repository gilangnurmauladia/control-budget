<?php

namespace App\Exports;

use App\Models\BudgetActual;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetActualExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected string $period;

    public function __construct(string $period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return BudgetActual::with(['department', 'item', 'item.category', 'location', 'creator'])
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$this->period])
            ->orderBy('transaction_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Transaksi', 'Departemen', 'Kode Item', 'Nama Item', 'Kategori',
            'Lokasi', 'Satuan', 'Qty Actual', 'Harga Satuan', 'Nominal (Rp)', 'Catatan', 'Input Oleh'
        ];
    }

    public function map($actual): array
    {
        return [
            $actual->transaction_date->format('d/m/Y'),
            $actual->department->dept_name,
            $actual->item->item_code,
            $actual->item->item_name,
            $actual->item->category->category_name ?? '-',
            $actual->location ? $actual->location->location_name : 'GENERAL',
            $actual->item->unit,
            $actual->actual_qty,
            $actual->unit_price_snapshot,
            $actual->actual_qty * $actual->unit_price_snapshot,
            $actual->notes ?? '-',
            $actual->creator->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
