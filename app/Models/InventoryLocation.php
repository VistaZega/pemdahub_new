<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $table = 'inventory_locations';

    protected $fillable = [
        'school_id',
        'name',
        'building_name',
        'room_number',
        'pic_name',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function assets()
    {
        return $this->hasMany(InventoryAsset::class, 'location_id');
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->name,
            $this->building_name ? "Gedung: {$this->building_name}" : null,
            $this->room_number ? "Ruang: {$this->room_number}" : null,
        ]);
        return implode(' - ', $parts);
    }
}
