<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlanRevision extends Model
{
    use HasFactory;

    protected $table = 'budget_plan_revisions';
    protected $fillable = [
        'budget_plan_id', 'revision_date', 'old_plan_qty', 'new_plan_qty',
        'version_from', 'version_to', 'reason_category', 'reason_detail', 'revised_by'
    ];

    protected $casts = [
        'revision_date' => 'datetime',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class, 'budget_plan_id');
    }

    public function reviser()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
