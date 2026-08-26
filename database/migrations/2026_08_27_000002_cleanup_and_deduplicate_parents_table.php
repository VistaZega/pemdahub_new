<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan dan perbaiki data orang tua untuk Celeste Nibenia Ogaena Zega
        $celeste = Student::where('full_name', 'LIKE', '%Celeste%')->first();
        if ($celeste) {
            // Temukan user Pak Yulianus Zega (Ketua Yayasan)
            $pakYulianus = User::where('username', 'yulzega')
                ->orWhere('email', 'yulzega@gmail.com')
                ->first();

            // Hapus data orang tua yang tidak valid untuk Celeste (misal: Erwin Setiawan Mendrofa atau user lain selain Yulianus)
            ParentModel::where('student_id', $celeste->id)
                ->where(function ($q) {
                    $q->where('full_name', 'LIKE', '%Erwin%')
                      ->orWhere('email', 'LIKE', '%erwinsm%');
                })->delete();

            // Jika ada lebih dari 1 record 'ayah' untuk Celeste, konsolidasikan menjadi 1 record tunggal
            $ayahRecords = ParentModel::where('student_id', $celeste->id)
                ->where('relation_type', 'ayah')
                ->get();

            if ($ayahRecords->count() > 1) {
                // Ambil record pertama untuk di-update, hapus sisanya
                $primary = $ayahRecords->first();
                $ayahRecords->slice(1)->each(fn($record) => $record->delete());

                $primary->update([
                    'user_id'       => $pakYulianus?->id ?? $primary->user_id,
                    'full_name'     => 'Yulianus Zega, S.Kom, M.Pd.T',
                    'relation_type' => 'ayah',
                    'occupation'    => 'Ketua Yayasan / PNS',
                    'phone'         => '0821 6853 2567',
                    'email'         => 'yulzega@gmail.com',
                ]);
            } elseif ($ayahRecords->count() === 1) {
                $ayahRecords->first()->update([
                    'user_id'       => $pakYulianus?->id ?? $ayahRecords->first()->user_id,
                    'full_name'     => 'Yulianus Zega, S.Kom, M.Pd.T',
                    'relation_type' => 'ayah',
                    'occupation'    => 'Ketua Yayasan / PNS',
                    'phone'         => '0821 6853 2567',
                    'email'         => 'yulzega@gmail.com',
                ]);
            }
        }

        // 2. Pembersihan duplikasi umum pada tabel parents (student_id + relation_type ganda)
        $duplicates = DB::table('parents')
            ->select('student_id', 'relation_type', DB::raw('COUNT(*) as count'))
            ->groupBy('student_id', 'relation_type')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $records = ParentModel::where('student_id', $dup->student_id)
                ->where('relation_type', $dup->relation_type)
                ->get();

            // Urutkan berdasarkan data terlengkap
            $best = $records->sortByDesc(function ($p) {
                $score = 0;
                if (!empty($p->user_id)) $score += 10;
                if (!empty($p->email) && $p->email !== '-') $score += 5;
                if (!empty($p->phone) && $p->phone !== '-') $score += 5;
                return $score;
            })->first();

            if ($best) {
                // Hapus yang bukan $best
                foreach ($records as $r) {
                    if ($r->id !== $best->id) {
                        $r->delete();
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down rollback needed for data cleanup
    }
};
