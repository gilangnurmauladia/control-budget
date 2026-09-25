<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetActual extends Model
{
    use HasFactory;

    protected $table = 'budget_actuals';
    protected $fillable = [
        'transaction_date', 'department_id', 'location_id', 'item_id',
        'actual_qty', 'unit_price_snapshot', 'notes', 'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
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

    // Nominal = qty x unit_price_snapshot
    public function getNominalAttribute()
    {
        return $this->actual_qty * $this->unit_price_snapshot;
    }
}
