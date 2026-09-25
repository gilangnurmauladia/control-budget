<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $table = 'master_item_categories';
    protected $fillable = ['category_name'];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
