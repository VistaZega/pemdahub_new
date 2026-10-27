<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use HasFactory;

    protected $table = 'inventory_categories';

    protected $fillable = [
        'name',
        'code',
        'useful_life_years',
        'depreciation_method',
        'description',
    ];

    public function assets()
    {
        return $this->hasMany(InventoryAsset::class, 'category_id');
    }
}
