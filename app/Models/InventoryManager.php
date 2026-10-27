<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryManager extends Model
{
    use HasFactory;

    protected $table = 'inventory_managers';

    protected $fillable = [
        'user_id',
        'school_id',
        'role_title',
        'can_create',
        'can_edit',
        'can_delete',
        'can_export',
        'is_active',
        'appointed_by',
        'appointed_at',
        'notes',
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
        'is_active' => 'boolean',
        'appointed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function appointedBy()
    {
        return $this->belongsTo(User::class, 'appointed_by');
    }
}
