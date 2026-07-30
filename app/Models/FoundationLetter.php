<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FoundationLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'title',
        'category',
        'content',
        'effective_date',
        'deadline_date',
        'recipients',
        'signatory_name',
        'signatory_position',
        'signature_hash',
        'signed_at',
        'signed_by_user_id',
        'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'deadline_date' => 'date',
        'signed_at' => 'datetime',
        'recipients' => 'array',
    ];

    public static function generateHash($letterNumber, $title, $effectiveDate)
    {
        return hash('sha256', $letterNumber . '|' . $title . '|' . $effectiveDate . '|' . Str::random(16) . '|' . config('app.key'));
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    public function reads()
    {
        return $this->hasMany(FoundationLetterRead::class, 'foundation_letter_id');
    }

    public function getVerificationUrlAttribute()
    {
        return route('public.letters.verify', $this->signature_hash);
    }

    public function getQrCodeUrlAttribute()
    {
        return "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($this->verification_url);
    }

    public function scopeForRole($query, $roleKey = 'guru_pegawai')
    {
        return $query->where(function ($q) use ($roleKey) {
            // Check multi-select array (target_keys)
            $q->whereJsonContains('recipients->target_keys', $roleKey)
              ->orWhereJsonContains('recipients->target_keys', 'all');

            // Check legacy string format (target_key)
            $q->orWhere('recipients->target_key', $roleKey)
              ->orWhere('recipients->target_key', 'all');

            if ($roleKey === 'guru_pegawai' || $roleKey === 'guru') {
                $q->orWhereJsonContains('recipients->target_keys', 'guru_pegawai');
            }
        });
    }

    public function scopeForUser($query, $user, $overrideRole = null)
    {
        if ($overrideRole) {
            return $this->scopeForRole($query, $overrideRole);
        }

        if (!$user) {
            return $query;
        }

        $role = $user->role ?? 'guru';

        // Superadmin & Ketua Yayasan in Yayasan management portal can see all letters
        if (in_array($role, ['superadmin', 'ketua_yayasan'])) {
            return $query;
        }

        if (in_array($role, ['guru', 'pegawai'])) {
            return $this->scopeForRole($query, 'guru_pegawai');
        }

        if (in_array($role, ['admin_sekolah', 'kepala_sekolah'])) {
            return $this->scopeForRole($query, 'kepala_sekolah');
        }

        if ($role === 'siswa') {
            return $this->scopeForRole($query, 'siswa');
        }

        return $this->scopeForRole($query, $role);
    }
}
