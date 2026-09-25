<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'department_id', 'is_active'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function budgetPlansCreated()
    {
        return $this->hasMany(BudgetPlan::class, 'created_by');
    }

    public function budgetActualsCreated()
    {
        return $this->hasMany(BudgetActual::class, 'created_by');
    }
}
