<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\Student;
use App\Models\BlockStudentGroup;
use App\Models\Applicant;
use App\Models\KonsentrasiKeahlian;
use App\Models\ProgramKeahlian;
use App\Models\Major;
use Illuminate\Support\Collection;

class TeachingAssignmentStudentFilterService
{
    /**
     * Dapatkan daftar siswa untuk suatu penugasan mengajar, 
     * dengan mempertimbangkan:
     * 1. Grup kelas gabungan (group_code)
     * 2. Filter kejuruan/jurusan (SMK/SMA Kelas Gabungan & Mapel Kejuruan)
     * 3. Filter paralel agama (Islam, Kristen, Katolik, dll.)
     * 4. Filter grup blok SMK (Kelompok A / B)
     */
    public function getStudentsForAssignment(TeachingAssignment $assignment): Collection
    {
        $activeYearId = $assignment->academic_year_id;
        $classroomId = $assignment->classroom_id;
        $subject = $assignment->subject;
        $classroom = $assignment->classroom;
        
        $studentsQuery = Student::whereHas('studentClasses', function ($q) use ($activeYearId) {
            $q->where('status', 'aktif')
              ->where('academic_year_id', $activeYearId);
        });

        // 1. Group Code (Gabungan Kelas)
        if (!empty($assignment->group_code)) {
            $relatedClassroomIds = TeachingAssignment::where('teacher_id', $assignment->teacher_id)
                ->where('academic_year_id', $activeYearId)
                ->where('group_code', $assignment->group_code)
                ->pluck('classroom_id')
                ->unique()
                ->toArray();
                
            $classroomIds = $relatedClassroomIds;
            $studentsQuery->whereHas('studentClasses', function($q) use ($relatedClassroomIds) {
                $q->whereIn('classroom_id', $relatedClassroomIds);
            })->with(['classrooms' => function($q) use ($relatedClassroomIds) {
                $q->whereIn('classrooms.id', $relatedClassroomIds);
            }]);
        } else {
            $classroomIds = [$classroomId];
            $studentsQuery->whereHas('studentClasses', function($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            })->with(['classrooms' => function($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            }]);
        }

        // 2. Filter Paralel (Agama)
        if ($assignment->block_type === 'parallel' && $subject) {
            $subjectName = strtolower(trim(($subject->name ?? '') . ' ' . ($subject->subject_name ?? '') . ' ' . ($subject->code ?? '') . ' ' . ($subject->subject_code ?? '')));
            
            if (str_contains($subjectName, 'islam') || str_contains($subjectName, 'pai') || preg_match('/\bpai\b/i', $subjectName)) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Islam')
                      ->orWhere('religion', 'like', '%islam%')
                      ->orWhere('religion', 'like', '%muslim%');
                });
            } elseif (str_contains($subjectName, 'katolik') || str_contains($subjectName, 'katholik') || str_contains($subjectName, 'p-kat') || str_contains($subjectName, 'pakat')) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Katolik')
                      ->orWhere('religion', 'Katholik')
                      ->orWhere('religion', 'like', '%katolik%')
                      ->orWhere('religion', 'like', '%katholik%');
                });
            } elseif (str_contains($subjectName, 'kristen') || str_contains($subjectName, 'protestan') || str_contains($subjectName, 'pak') || preg_match('/\bpak\b/i', $subjectName)) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Kristen')
                      ->orWhere('religion', 'Kristen Protestan')
                      ->orWhere('religion', 'Protestan')
                      ->orWhere('religion', 'like', '%kristen%')
                      ->orWhere('religion', 'like', '%protestan%');
                });
            } elseif (str_contains($subjectName, 'hindu')) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Hindu')
                      ->orWhere('religion', 'like', '%hindu%');
                });
            } elseif (str_contains($subjectName, 'buddha') || str_contains($subjectName, 'budha')) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Buddha')
                      ->orWhere('religion', 'Budha')
                      ->orWhere('religion', 'like', '%buddha%')
                      ->orWhere('religion', 'like', '%budha%');
                });
            } elseif (str_contains($subjectName, 'konghucu') || str_contains($subjectName, 'khonghucu')) {
                $studentsQuery->where(function($q) {
                    $q->where('religion', 'Konghucu')
                      ->orWhere('religion', 'Khonghucu')
                      ->orWhere('religion', 'like', '%konghucu%');
                });
            }
        }

        // 3. Filter Kejuruan / Jurusan (Khusus Mapel Kejuruan)
        if ($subject) {
            $targetProgramId = $subject->program_keahlian_id;
            $targetMajorId = $subject->major_id;
            
            // Deteksi jika program_keahlian_id belum diset di tabel subjects
            if (!$targetProgramId && !$targetMajorId) {
                $subjectText = strtoupper(($subject->name ?? '') . ' ' . ($subject->subject_name ?? '') . ' ' . ($subject->code ?? '') . ' ' . ($subject->subject_code ?? ''));
                
                if (preg_match('/\b(DPIB|BANGUNAN|GAMBAR BANGUNAN)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::where('kode', 'DPIB')->orWhere('nama', 'like', '%Bangunan%')->orWhere('nama', 'like', '%Desain Pemodelan%')->value('id');
                } elseif (preg_match('/\b(TJKT|TKJ|JARINGAN|KOMPUTER DAN JARINGAN)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::whereIn('kode', ['TKJ', 'TJKT'])->orWhere('nama', 'like', '%Jaringan%')->value('id');
                } elseif (preg_match('/\b(TSM|TBSM|SEPEDA MOTOR)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::whereIn('kode', ['TSM', 'TBSM'])->orWhere('nama', 'like', '%Sepeda Motor%')->value('id');
                } elseif (preg_match('/\b(TKR|KENDARAAN RINGAN)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::where('kode', 'TKR')->orWhere('nama', 'like', '%Kendaraan Ringan%')->value('id');
                } elseif (preg_match('/\b(TAV|AUDIO VIDEO)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::where('kode', 'TAV')->orWhere('nama', 'like', '%Audio Video%')->orWhere('kode', 'TE')->orWhere('nama', 'like', '%Elektronika%')->value('id');
                } elseif (preg_match('/\b(TE|ELEKTRONIKA)\b/i', $subjectText)) {
                    $targetProgramId = ProgramKeahlian::where('kode', 'TE')->orWhere('nama', 'like', '%Elektronika%')->orWhere('kode', 'TAV')->orWhere('nama', 'like', '%Audio Video%')->value('id');
                } elseif (preg_match('/\b(MIPA|IPA)\b/i', $subjectText)) {
                    $targetMajorId = Major::where('code', 'IPA')->orWhere('name', 'like', '%IPA%')->value('id');
                } elseif (preg_match('/\b(IPS)\b/i', $subjectText)) {
                    $targetMajorId = Major::where('code', 'IPS')->orWhere('name', 'like', '%IPS%')->value('id');
                }
            }
            
            if ($targetProgramId || $targetMajorId) {
                // Cari siswa aktif di kelas ini yang memiliki kecocokan jurusan
                $activeStudentIdsInClass = Student::whereHas('studentClasses', function ($q) use ($activeYearId, $classroomIds) {
                    $q->where('status', 'aktif')
                      ->where('academic_year_id', $activeYearId)
                      ->whereIn('classroom_id', $classroomIds);
                })->pluck('id')->toArray();

                // A. Dari Applicant (PSB)
                $applicantQuery = Applicant::where(function($q) use ($targetProgramId, $targetMajorId) {
                    if ($targetProgramId) {
                        $konsentrasiIds = KonsentrasiKeahlian::where('program_keahlian_id', $targetProgramId)->pluck('id')->toArray();
                        $q->where('program_keahlian_id', $targetProgramId)
                          ->orWhereIn('konsentrasi_keahlian_id', $konsentrasiIds);
                    }
                    if ($targetMajorId) {
                        $q->orWhere('major_id', $targetMajorId);
                    }
                })
                ->whereIn('student_id', $activeStudentIdsInClass);

                $matchedStudentIds = $applicantQuery->pluck('student_id')->toArray();

                // B. Dari riwayat kelas jurusan siswa
                $classroomMatchedIds = Student::whereIn('id', $activeStudentIdsInClass)
                    ->whereHas('classrooms', function($q) use ($targetProgramId, $targetMajorId) {
                        if ($targetProgramId) {
                            $q->where('program_keahlian_id', $targetProgramId);
                        }
                        if ($targetMajorId) {
                            $q->where('major_id', $targetMajorId);
                        }
                    })->pluck('id')->toArray();

                $allMatched = array_unique(array_merge($matchedStudentIds, $classroomMatchedIds));

                // Hanya filter jika ditemukan kecocokan siswa di kelas ini
                if (!empty($allMatched)) {
                    $studentsQuery->whereIn('id', $allMatched);
                }
            }
        }

        // 4. Filter SMK Block System
        // 'all' = Group A
        // 'split' = Group B
        if (in_array($assignment->block_type, ['all', 'split'])) {
            $targetGroup = $assignment->block_type === 'all' ? 'A' : 'B';
            
            $classroomIdsForBlock = !empty($assignment->group_code) && isset($relatedClassroomIds) 
                ? $relatedClassroomIds 
                : [$classroomId];

            // Cek apakah kelas ini memiliki data pembagian grup blok
            $hasBlockData = BlockStudentGroup::whereIn('classroom_id', $classroomIdsForBlock)->exists();

            if ($hasBlockData) {
                $validStudentIds = BlockStudentGroup::whereIn('classroom_id', $classroomIdsForBlock)
                    ->where('group', $targetGroup)
                    ->pluck('student_id')
                    ->toArray();
                    
                if (!empty($validStudentIds)) {
                    $studentsQuery->whereIn('id', $validStudentIds);
                }
            }
        }

        $results = $studentsQuery->orderBy('full_name')->get();

        // Safety fallback: jika hasil kosong, kembalikan semua siswa aktif kelas agar tidak terblok
        if ($results->isEmpty()) {
            $fallbackQuery = Student::whereHas('studentClasses', function ($q) use ($activeYearId, $classroomIds) {
                $q->where('status', 'aktif')
                  ->where('academic_year_id', $activeYearId)
                  ->whereIn('classroom_id', $classroomIds);
            });
            return $fallbackQuery->orderBy('full_name')->get();
        }

        return $results;
    }
}
