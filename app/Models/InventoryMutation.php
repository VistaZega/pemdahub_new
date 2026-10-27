<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMutation extends Model
{
    use HasFactory;

    protected $table = 'inventory_mutations';

    protected $fillable = [
        'asset_id',
        'mutation_date',
        'type',
        'from_location_id',
        'to_location_id',
        'from_condition',
        'to_condition',
        'cost',
        'description',
        'performed_by',
    ];

    protected $casts = [
        'mutation_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(InventoryAsset::class, 'asset_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'to_location_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
