<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Spesifikasi 5 Unit Sekolah Resmi Pembda
        $canonicalSpecs = [
            [
                'canonical_name' => 'SMK Swasta Pembda Nias',
                'type' => 'SMK',
                'is_active' => true,
                'psb_is_active' => true,
                'patterns' => ['%SMK%Swasta%Pembda%', '%SMKS%Pembda%', '%STM%Pembda%', '%STM%'],
            ],
            [
                'canonical_name' => 'SMAS Pembda 1 Gunungsitoli',
                'type' => 'SMA',
                'is_active' => true,
                'psb_is_active' => true,
                'patterns' => ['%SMA%1%', '%SMAS%1%'],
            ],
            [
                'canonical_name' => 'SMAS Pembda 2 Gunungsitoli',
                'type' => 'SMA',
                'is_active' => false,
                'psb_is_active' => false,
                'patterns' => ['%SMA%2%', '%SMAS%2%'],
            ],
            [
                'canonical_name' => 'SMPS Pembda 2 Gunungsitoli',
                'type' => 'SMP',
                'is_active' => true,
                'psb_is_active' => true,
                'patterns' => ['%SMP%2%', '%SMPS%2%'],
            ],
            [
                'canonical_name' => 'SMPS Pembda 1 Gunungsitoli',
                'type' => 'SMP',
                'is_active' => false,
                'psb_is_active' => false,
                'patterns' => ['%SMP%1%', '%SMPS%1%'],
            ],
        ];

        // Cari SEMUA tabel di database yang memiliki kolom school_id secara dinamis
        $allTables = [];
        try {
            $databaseName = DB::getDatabaseName();
            $rows = DB::select("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = 'school_id' AND TABLE_SCHEMA = ?", [$databaseName]);
            foreach ($rows as $r) {
                $tblName = $r->TABLE_NAME ?? $r->table_name ?? null;
                if ($tblName && $tblName !== 'schools') {
                    $allTables[] = $tblName;
                }
            }
        } catch (\Throwable $e) {
            // Fallback untuk driver seperti SQLite
            $tables = Schema::getTableListing();
            foreach ($tables as $t) {
                if ($t !== 'schools' && Schema::hasColumn($t, 'school_id')) {
                    $allTables[] = $t;
                }
            }
        }

        foreach ($canonicalSpecs as $spec) {
            // Cari sekolah yang cocok dengan pattern (abaikan tipe yayasan)
            $matchingSchools = DB::table('schools')
                ->where('type', '!=', 'yayasan')
                ->where(function($q) use ($spec) {
                    foreach ($spec['patterns'] as $idx => $pattern) {
                        if ($idx === 0) {
                            $q->where('name', 'LIKE', $pattern);
                        } else {
                            $q->orWhere('name', 'LIKE', $pattern);
                        }
                    }
                })->get();

            if ($matchingSchools->isEmpty()) {
                // Insert sekolah canonical baru jika belum ada
                DB::table('schools')->insert([
                    'name' => $spec['canonical_name'],
                    'type' => $spec['type'],
                    'is_active' => $spec['is_active'],
                    'psb_is_active' => $spec['psb_is_active'],
                    'city' => 'Gunungsitoli',
                    'province' => 'Sumatera Utara',
                ]);
            } else {
                // Tentukan 1 record canonical utama
                $canonicalRecord = $matchingSchools->firstWhere('name', $spec['canonical_name']) ?? $matchingSchools->first();
                $canonicalId = $canonicalRecord->id;

                // Update nama dan status sekolah utama
                DB::table('schools')->where('id', $canonicalId)->update([
                    'name' => $spec['canonical_name'],
                    'type' => $spec['type'],
                    'is_active' => $spec['is_active'],
                ]);

                // Ambil daftar ID duplikat
                $duplicateIds = $matchingSchools->pluck('id')->reject(fn($id) => $id == $canonicalId)->all();

                if (!empty($duplicateIds)) {
                    // 1. Re-link kolom school_id pada SELURUH tabel secara dinamis & aman
                    foreach ($allTables as $tbl) {
                        if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'school_id')) {
                            $rows = DB::table($tbl)->whereIn('school_id', $duplicateIds)->get();
                            foreach ($rows as $row) {
                                try {
                                    DB::table($tbl)->where('id', $row->id)->update(['school_id' => $canonicalId]);
                                } catch (\Throwable $e) {
                                    // Jika terjadi bentrokan unique key (1062), hapus baris duplikat karena data canonical sudah ada
                                    DB::table($tbl)->where('id', $row->id)->delete();
                                }
                            }
                        }
                    }

                    // 2. Hapus record duplikat dari tabel schools dengan mengabaikan foreign key checks sementara (cegah error 1451)
                    try {
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    } catch (\Throwable $e) {}

                    DB::table('schools')->whereIn('id', $duplicateIds)->delete();

                    try {
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    } catch (\Throwable $e) {}
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse
    }
};
