<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetOverReason extends Model
{
    use HasFactory;

    protected $table = 'budget_over_reasons';
    protected $fillable = [
        'budget_plan_id', 'flag_date', 'reason_category', 'reason_detail', 'submitted_by'
    ];

    protected $casts = [
        'flag_date' => 'date',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class, 'budget_plan_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
