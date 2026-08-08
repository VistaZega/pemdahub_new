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
                'patterns' => ['%SMK%Swasta%Pembda%', '%SMKS%Pembda%'],
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

        // Tabel-tabel yang terhubung dengan school_id
        $tablesWithSchoolId = [
            'users',
            'students',
            'classrooms',
            'teachers',
            'employees',
            'subjects',
            'majors',
            'schedules',
            'alumni_directories',
            'alumni_profiles',
            'alumni',
            'alumni_forums',
            'lms_courses',
            'school_contributions',
            'registration_waves',
            'admission_fees',
            'admission_tests',
            'grade_weights',
            'operational_expenses',
            'teacher_schools',
        ];

        foreach ($canonicalSpecs as $spec) {
            // Cari sekolah yang cocok dengan pattern
            $query = DB::table('schools')->where('type', '!=', 'yayasan')->where(function($q) use ($spec) {
                foreach ($spec['patterns'] as $idx => $pattern) {
                    if ($idx === 0) {
                        $q->where('name', 'LIKE', $pattern);
                    } else {
                        $q->orWhere('name', 'LIKE', $pattern);
                    }
                }
            });

            $matchingSchools = $query->get();

            if ($matchingSchools->isEmpty()) {
                // Buat record baru jika belum ada sama sekali
                DB::table('schools')->insert([
                    'name' => $spec['canonical_name'],
                    'type' => $spec['type'],
                    'is_active' => $spec['is_active'],
                    'psb_is_active' => $spec['psb_is_active'],
                    'city' => 'Gunungsitoli',
                    'province' => 'Sumatera Utara',
                ]);
            } else {
                // Pilih 1 record utama (canonical)
                $canonicalRecord = $matchingSchools->firstWhere('name', $spec['canonical_name']) ?? $matchingSchools->first();
                $canonicalId = $canonicalRecord->id;

                // Update nama dan status sekolah utama
                DB::table('schools')->where('id', $canonicalId)->update([
                    'name' => $spec['canonical_name'],
                    'type' => $spec['type'],
                    'is_active' => $spec['is_active'],
                ]);

                // Identifikasi ID duplikat
                $duplicateIds = $matchingSchools->pluck('id')->reject(fn($id) => $id == $canonicalId)->all();

                if (!empty($duplicateIds)) {
                    // Re-link relasi school_id di seluruh tabel ke sekolah utama (dengan try-catch untuk bentrokan unique constraint)
                    foreach ($tablesWithSchoolId as $tbl) {
                        if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'school_id')) {
                            $rows = DB::table($tbl)->whereIn('school_id', $duplicateIds)->get();
                            foreach ($rows as $row) {
                                try {
                                    DB::table($tbl)->where('id', $row->id)->update(['school_id' => $canonicalId]);
                                } catch (\Throwable $e) {
                                    // Jika terjadi bentrokan unique constraint (1062), hapus baris duplikat karena data canonical sudah ada
                                    DB::table($tbl)->where('id', $row->id)->delete();
                                }
                            }
                        }
                    }

                    // Hapus record duplikat dari tabel schools
                    DB::table('schools')->whereIn('id', $duplicateIds)->delete();
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
