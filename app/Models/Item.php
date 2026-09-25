<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'master_items';
    protected $fillable = ['category_id', 'item_code', 'item_name', 'unit', 'unit_price', 'status'];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_item', 'item_id', 'department_id');
    }

    public function budgetPlans()
    {
        return $this->hasMany(BudgetPlan::class, 'item_id');
    }

    public function budgetActuals()
    {
        return $this->hasMany(BudgetActual::class, 'item_id');
    }
}
