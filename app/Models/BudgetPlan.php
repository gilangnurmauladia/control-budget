<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlan extends Model
{
    use HasFactory;

    protected $table = 'budget_plans';
    protected $fillable = [
        'period', 'department_id', 'location_id', 'item_id',
        'plan_qty', 'unit_price_snapshot', 'current_version', 'created_by'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisions()
    {
        return $this->hasMany(BudgetPlanRevision::class, 'budget_plan_id');
    }

    public function overReasons()
    {
        return $this->hasMany(BudgetOverReason::class, 'budget_plan_id');
    }

    // Hitung total actual untuk plan ini (periode + dept + item)
    public function getTotalActualAttribute()
    {
        return BudgetActual::where('department_id', $this->department_id)
            ->where('item_id', $this->item_id)
            ->whereYear('transaction_date', substr($this->period, 0, 4))
            ->whereMonth('transaction_date', substr($this->period, 5, 2))
            ->sum('actual_qty');
    }

    // Hitung persentase serapan
    public function getAbsorptionPercentAttribute()
    {
        if ($this->plan_qty == 0) return 0;
        return round(($this->total_actual / $this->plan_qty) * 100, 2);
    }

    // Status over-budget
    public function getIsOverBudgetAttribute()
    {
        return $this->absorption_percent > 100;
    }
}
