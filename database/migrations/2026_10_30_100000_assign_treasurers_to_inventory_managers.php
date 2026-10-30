<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hapus penunjukan sebelumnya untuk dewi@pembdahub.com
        $dewiUser = User::where('email', 'dewi@pembdahub.com')->orWhere('username', 'dewi')->first();
        if ($dewiUser) {
            DB::table('inventory_managers')->where('user_id', $dewiUser->id)->delete();
        }

        // 2. Daftarkan seluruh akun bendahara ke inventory_managers dengan Hak Akses Penuh
        $treasurers = [
            [
                'email' => 'bendaharasmp@pembdahub.com',
                'username' => 'bendaharasmp',
                'school_id' => 9,
                'role_title' => 'Bendahara & Pengelola Aset SMPS Pembda 2',
            ],
            [
                'email' => 'bendaharasmk@pembdahub.com',
                'username' => 'bendaharasmk',
                'school_id' => 7,
                'role_title' => 'Bendahara & Pengelola Aset SMK Swasta Pembda Nias (Loket 1)',
            ],
            [
                'email' => 'bendaharasmk2@pembdahub.com',
                'username' => 'bendaharasmk2',
                'school_id' => 7,
                'role_title' => 'Bendahara & Pengelola Aset SMK Swasta Pembda Nias (Loket 2)',
            ],
            [
                'email' => 'bendaharasma@pembdahub.com',
                'username' => 'bendaharasma',
                'school_id' => 2,
                'role_title' => 'Bendahara & Pengelola Aset SMAS Pembda 1 Gunungsitoli',
            ],
            [
                'email' => 'yuniria@pembdahub.com',
                'username' => 'yuniria',
                'school_id' => 2,
                'role_title' => 'Staff Keuangan & Pengelola Aset SMAS Pembda 1',
            ],
            [
                'email' => 'bendaharayayasan@pembdahub.com',
                'username' => 'bendaharayayasan',
                'school_id' => 4,
                'role_title' => 'Bendahara Yayasan & Pengawas Aset Terpadu',
            ],
        ];

        foreach ($treasurers as $t) {
            $user = User::where('email', $t['email'])->orWhere('username', $t['username'])->first();
            if ($user) {
                DB::table('inventory_managers')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'school_id' => $t['school_id'],
                        'role_title' => $t['role_title'],
                        'can_create' => 1,
                        'can_edit' => 1,
                        'can_delete' => 1,
                        'can_export' => 1,
                        'is_active' => 1,
                        'appointed_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $emails = [
            'bendaharasmp@pembdahub.com',
            'bendaharasmk@pembdahub.com',
            'bendaharasmk2@pembdahub.com',
            'bendaharasma@pembdahub.com',
            'yuniria@pembdahub.com',
            'bendaharayayasan@pembdahub.com',
        ];

        $userIds = User::whereIn('email', $emails)->pluck('id');
        DB::table('inventory_managers')->whereIn('user_id', $userIds)->delete();
    }
};
