<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'master_departments';
    protected $fillable = ['dept_code', 'dept_name', 'is_active'];

    public function locations()
    {
        return $this->hasMany(Location::class, 'department_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function budgetPlans()
    {
        return $this->hasMany(BudgetPlan::class, 'department_id');
    }

    public function budgetActuals()
    {
        return $this->hasMany(BudgetActual::class, 'department_id');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'department_item', 'department_id', 'item_id');
    }
}
