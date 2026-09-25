<?php

namespace App\Exports;

use App\Models\BudgetPlan;
use App\Models\BudgetActual;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected string $period;
    protected ?int $deptId;

    public function __construct(string $period, ?int $deptId = null)
    {
        $this->period = $period;
        $this->deptId = $deptId;
    }

    public function collection()
    {
        $query = BudgetPlan::with(['department', 'item', 'item.category'])
            ->where('period', $this->period);
        if ($this->deptId) $query->where('department_id', $this->deptId);

        return $query->get()->map(function ($plan) {
            $actualQty = BudgetActual::where('department_id', $plan->department_id)
                ->where('item_id', $plan->item_id)
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$this->period])
                ->sum('actual_qty');
            $plan->actual_qty   = $actualQty;
            $plan->pct          = $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0;
            return $plan;
        });
    }

    public function headings(): array
    {
        return [
            'Departemen', 'Kategori', 'Kode Item', 'Nama Item', 'Satuan', 'Harga Satuan (Rp)',
            'Plan Qty', 'Plan Nominal (Rp)',
            'Actual Qty', 'Actual Nominal (Rp)',
            'Sisa Qty', 'Sisa Nominal (Rp)',
            '% Serapan', 'Status', 'Versi Plan',
        ];
    }

    public function map($plan): array
    {
        $sisaQty = $plan->plan_qty - $plan->actual_qty;
        return [
            $plan->department->dept_name,
            $plan->item->category->category_name ?? '-',
            $plan->item->item_code,
            $plan->item->item_name,
            $plan->item->unit,
            $plan->unit_price_snapshot,
            $plan->plan_qty,
            $plan->plan_qty * $plan->unit_price_snapshot,
            $plan->actual_qty,
            $plan->actual_qty * $plan->unit_price_snapshot,
            $sisaQty,
            $sisaQty * $plan->unit_price_snapshot,
            $plan->pct . '%',
            $plan->pct > 100 ? 'OVER BUDGET' : 'NORMAL',
            'v' . $plan->current_version,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Budget ' . $this->period;
    }
}
