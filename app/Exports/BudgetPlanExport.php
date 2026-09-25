<?php

namespace App\Exports;

use App\Models\BudgetPlan;
use App\Models\BudgetActual;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetPlanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        
        if ($this->deptId) {
            $query->where('department_id', $this->deptId);
        }

        return $query->orderBy('department_id')
            ->orderBy('item_id')
            ->get()
            ->map(function ($plan) {
                $actualQty = BudgetActual::where('department_id', $plan->department_id)
                    ->where('item_id', $plan->item_id)
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$this->period])
                    ->sum('actual_qty');
                $plan->actual_qty   = $actualQty;
                $plan->actual_nom   = $actualQty * $plan->unit_price_snapshot;
                $plan->plan_nom     = $plan->plan_qty * $plan->unit_price_snapshot;
                $plan->pct          = $plan->plan_qty > 0 ? round(($actualQty / $plan->plan_qty) * 100, 1) : 0;
                return $plan;
            });
    }

    public function headings(): array
    {
        return [
            'Periode', 'Departemen', 'Kode Item', 'Nama Item', 'Kategori', 'Satuan', 'Harga Satuan',
            'Plan Qty', 'Plan Nominal (Rp)', 'Actual Qty', 'Actual Nominal (Rp)',
            'Sisa Qty', 'Sisa Nominal (Rp)', '% Serapan', 'Status', 'Versi Plan',
        ];
    }

    public function map($plan): array
    {
        $sisaQty = $plan->plan_qty - $plan->actual_qty;
        return [
            $this->period,
            $plan->department->dept_name,
            $plan->item->item_code,
            $plan->item->item_name,
            $plan->item->category->category_name ?? '-',
            $plan->item->unit,
            $plan->unit_price_snapshot,
            $plan->plan_qty,
            $plan->plan_nom,
            $plan->actual_qty,
            $plan->actual_nom,
            $sisaQty,
            $sisaQty * $plan->unit_price_snapshot,
            $plan->pct . '%',
            $plan->pct > 100 ? 'OVER BUDGET' : 'NORMAL',
            'v' . $plan->current_version,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A5F']]]];
    }

    public function title(): string
    {
        return 'Budget Plan ' . $this->period;
    }
}
