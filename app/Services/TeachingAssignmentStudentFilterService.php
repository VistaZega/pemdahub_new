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
                
            $studentsQuery->whereHas('studentClasses', function($q) use ($relatedClassroomIds) {
                $q->whereIn('classroom_id', $relatedClassroomIds);
            })->with(['classrooms' => function($q) use ($relatedClassroomIds) {
                $q->whereIn('classrooms.id', $relatedClassroomIds);
            }]);
        } else {
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

        // 3. Filter Kejuruan / Jurusan (Khusus Mapel Kejuruan atau Kelas Gabungan)
        if ($subject) {
            $targetProgramId = $subject->program_keahlian_id;
            $targetMajorId = $subject->major_id;
            
            // Deteksi otomatis jika program/major belum terhubung di tabel subjects
            if (!$targetProgramId && !$targetMajorId) {
                $subjectText = strtoupper(($subject->name ?? '') . ' ' . ($subject->subject_name ?? '') . ' ' . ($subject->code ?? '') . ' ' . ($subject->subject_code ?? ''));
                $keywords = [
                    'DPIB' => ['DPIB', 'BANGUNAN', 'GAMBAR BANGUNAN'],
                    'TJKT' => ['TJKT', 'TKJ', 'JARINGAN', 'KOMPUTER DAN JARINGAN'],
                    'TSM'  => ['TSM', 'TBSM', 'SEPEDA MOTOR'],
                    'TKR'  => ['TKR', 'KENDARAAN RINGAN', 'OTOMOTIF'],
                    'TAV'  => ['TAV', 'AUDIO VIDEO'],
                    'TE'   => ['TE', 'ELEKTRONIKA'],
                    'IPA'  => ['MIPA', 'IPA'],
                    'IPS'  => ['IPS'],
                ];
                
                foreach ($keywords as $code => $patterns) {
                    foreach ($patterns as $p) {
                        if (preg_match('/\b' . preg_quote($p, '/') . '\b/i', $subjectText)) {
                            $prog = ProgramKeahlian::where('kode', 'like', "%{$code}%")
                                ->orWhere('nama', 'like', "%{$code}%")
                                ->first();
                            if ($prog) {
                                $targetProgramId = $prog->id;
                                break 2;
                            }
                            
                            $maj = Major::where('code', 'like', "%{$code}%")
                                ->orWhere('name', 'like', "%{$code}%")
                                ->first();
                            if ($maj) {
                                $targetMajorId = $maj->id;
                                break 2;
                            }
                        }
                    }
                }
            }
            
            // Jika mapel ini terikat pada Program Keahlian / Jurusan tertentu
            if ($targetProgramId || $targetMajorId) {
                // Temukan siswa yang jurusannya cocok dari data Applicant (Pendaftaran PSB)
                $applicantQuery = Applicant::where(function($q) use ($targetProgramId, $targetMajorId) {
                    if ($targetProgramId) {
                        $konsentrasiIds = KonsentrasiKeahlian::where('program_keahlian_id', $targetProgramId)->pluck('id')->toArray();
                        $q->where('program_keahlian_id', $targetProgramId)
                          ->orWhereIn('konsentrasi_keahlian_id', $konsentrasiIds);
                    }
                    if ($targetMajorId) {
                        $q->orWhere('major_id', $targetMajorId);
                    }
                });

                $applicantStudentIds = (clone $applicantQuery)->whereNotNull('student_id')->pluck('student_id')->toArray();
                $applicantNisns = (clone $applicantQuery)->whereNotNull('nisn')->pluck('nisn')->toArray();

                // Cek juga dari riwayat kelas reguler siswa
                $classroomStudentIds = Student::whereHas('classrooms', function($q) use ($targetProgramId, $targetMajorId) {
                    if ($targetProgramId) {
                        $q->where('program_keahlian_id', $targetProgramId);
                    }
                    if ($targetMajorId) {
                        $q->where('major_id', $targetMajorId);
                    }
                })->pluck('id')->toArray();

                $allMatchedIds = array_unique(array_merge($applicantStudentIds, $classroomStudentIds));

                // Jika ada siswa yang cocok, filter daftar siswa ke jurusan tersebut
                if (!empty($allMatchedIds) || !empty($applicantNisns)) {
                    $studentsQuery->where(function($q) use ($allMatchedIds, $applicantNisns) {
                        if (!empty($allMatchedIds)) {
                            $q->whereIn('id', $allMatchedIds);
                        }
                        if (!empty($applicantNisns)) {
                            $q->orWhereIn('nisn', $applicantNisns);
                        }
                    });
                }
            }
        }

        // 4. Filter SMK Block System
        // 'all' = Group A
        // 'split' = Group B
        if (in_array($assignment->block_type, ['all', 'split'])) {
            $targetGroup = $assignment->block_type === 'all' ? 'A' : 'B';
            
            // Get student IDs that belong to this group for the relevant classrooms
            $classroomIdsForBlock = !empty($assignment->group_code) && isset($relatedClassroomIds) 
                ? $relatedClassroomIds 
                : [$classroomId];

            $validStudentIds = BlockStudentGroup::whereIn('classroom_id', $classroomIdsForBlock)
                ->where('group', $targetGroup)
                ->pluck('student_id')
                ->toArray();
                
            if (!empty($validStudentIds)) {
                $studentsQuery->whereIn('id', $validStudentIds);
            } else {
                // If no group is mapped but it's supposed to be filtered, return empty.
                $studentsQuery->whereIn('id', [0]);
            }
        }

        return $studentsQuery->orderBy('full_name')->get();
    }
}
