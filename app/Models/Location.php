<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'master_locations';
    protected $fillable = ['department_id', 'location_code', 'location_name', 'is_default', 'status'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function budgetPlans()
    {
        return $this->hasMany(BudgetPlan::class, 'location_id');
    }
}
