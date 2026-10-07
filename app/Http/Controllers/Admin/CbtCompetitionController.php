<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtExam;
use App\Models\CbtCompetitionTeam;
use App\Models\CbtCompetitionTeamMember;
use App\Models\CbtExamResult;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CbtCompetitionController extends Controller
{
    /**
     * Halaman manajemen tim untuk ujian lomba tertentu
     */
    public function teams(CbtExam $exam)
    {
        abort_unless($exam->participation_mode === 'team', 404, 'Ujian ini bukan mode tim.');

        $teams = $exam->competitionTeams()
            ->with(['classroom', 'members.student.user', 'sessions.result'])
            ->orderBy('id')
            ->get();

        // Kelas yang tersedia (sekolah aktif, belum punya tim untuk ujian ini)
        $assignedClassroomIds = $teams->pluck('classroom_id')->filter()->toArray();
        $availableClassrooms = Classroom::whereHas('school', fn($q) => $q->schoolsOnly())
            ->whereNotIn('id', $assignedClassroomIds)
            ->orderBy('class_name')
            ->get();

        return view('admin.cbt.competition.teams', compact('exam', 'teams', 'availableClassrooms'));
    }

    /**
     * Simpan tim baru / update tim yang sudah ada
     */
    public function storeTeam(Request $request, CbtExam $exam)
    {
        abort_unless($exam->participation_mode === 'team', 403);

        $validated = $request->validate([
            'classroom_id'    => 'required|exists:classrooms,id',
            'team_name'       => 'nullable|string|max:100',
            'operator_id'     => 'required|exists:students,id',
            'member_ids'      => 'nullable|array',
            'member_ids.*'    => 'exists:students,id',
        ], [
            'classroom_id.required' => 'Pilih kelas yang akan diwakili.',
            'operator_id.required'  => 'Pilih siswa operator (yang akan mengerjakan soal).',
        ]);

        DB::transaction(function () use ($validated, $exam) {
            $team = CbtCompetitionTeam::updateOrCreate(
                ['exam_id' => $exam->id, 'classroom_id' => $validated['classroom_id']],
                [
                    'team_name'  => $validated['team_name'] ?: null,
                    'created_by' => Auth::id(),
                ]
            );

            // Susun ulang semua anggota tim
            $team->members()->delete();

            $studentIds = collect($validated['member_ids'] ?? [])
                ->push($validated['operator_id'])
                ->unique()
                ->values();

            foreach ($studentIds as $studentId) {
                CbtCompetitionTeamMember::create([
                    'team_id'     => $team->id,
                    'student_id'  => $studentId,
                    'is_operator' => $studentId == $validated['operator_id'],
                ]);
            }
        });

        return back()->with('success', 'Tim berhasil disimpan.');
    }

    /**
     * Hapus tim
     */
    public function destroyTeam(CbtExam $exam, CbtCompetitionTeam $team)
    {
        abort_unless($team->exam_id === $exam->id, 403);
        $team->delete();

        return back()->with('success', 'Tim berhasil dihapus.');
    }

    /**
     * Ambil data siswa dari kelas tertentu (untuk AJAX dropdown)
     */
    public function getStudentsByClassroom(Request $request)
    {
        $classroomId = $request->integer('classroom_id');

        $students = Student::whereHas('classes', fn($q) =>
            $q->where('classroom_id', $classroomId)->where('status', 'aktif')
        )
        ->with('user')
        ->orderBy('full_name')
        ->get(['id', 'full_name', 'nis']);

        return response()->json($students);
    }

    /**
     * Halaman Livescore / Leaderboard publik (untuk proyektor)
     * Tidak memerlukan login — diakses via token ujian
     */
    public function livescore(CbtExam $exam)
    {
        abort_unless(in_array($exam->status, ['active', 'completed']), 403, 'Ujian belum dimulai.');

        return view('cbt.livescore', compact('exam'));
    }

    /**
     * API endpoint polling data leaderboard (dipanggil setiap 2-3 detik via AJAX)
     */
    public function livescoreData(CbtExam $exam)
    {
        abort_unless(in_array($exam->status, ['active', 'completed']), 403);

        $isTeamMode = $exam->participation_mode === 'team';

        if ($isTeamMode) {
            $leaderboard = $this->getTeamLeaderboard($exam);
        } else {
            $leaderboard = $this->getIndividualLeaderboard($exam);
        }

        $stats = [
            'total_teams'      => $isTeamMode ? $exam->competitionTeams()->count() : 0,
            'active_sessions'  => $exam->sessions()->where('status', 'in_progress')->count(),
            'finished_sessions'=> $exam->sessions()->whereIn('status', ['submitted', 'timeout', 'graded'])->count(),
            'exam_title'       => $exam->exam_title,
            'scoring_info'     => $exam->scoring_mode === 'competition'
                ? "Benar: +{$exam->correct_points} | Salah: -{$exam->wrong_penalty} | Kosong: 0"
                : 'Penilaian standar',
            'is_paused'        => $exam->is_paused,
        ];

        return response()->json([
            'leaderboard' => $leaderboard,
            'stats'       => $stats,
            'timestamp'   => now()->format('H:i:s'),
        ]);
    }

    /**
     * Leaderboard mode tim: ranking berdasarkan skor tim (operator)
     */
    private function getTeamLeaderboard(CbtExam $exam): array
    {
        $teams = $exam->competitionTeams()
            ->with(['classroom', 'operator.student.user', 'sessions' => function ($q) {
                $q->with('result')->orderByDesc('id')->limit(1);
            }])
            ->get();

        $data = $teams->map(function ($team) {
            $latestSession = $team->sessions->first();
            $result = $latestSession?->result;

            $status = 'waiting'; // Belum mulai
            if ($latestSession) {
                $status = match($latestSession->status) {
                    'in_progress' => 'ongoing',
                    'submitted', 'graded', 'timeout' => 'done',
                    default => 'waiting',
                };
            }

            return [
                'team_id'      => $team->id,
                'display_name' => $team->display_name,
                'class_name'   => $team->classroom?->class_name ?? '-',
                'score'        => $result?->final_score ?? null,
                'correct'      => $result?->correct_answers ?? 0,
                'wrong'        => $result?->wrong_answers ?? 0,
                'unanswered'   => $result?->unanswered ?? 0,
                'status'       => $status,
                'time_spent'   => $result?->time_spent_seconds ?? 0,
            ];
        });

        // Sort: done & skor tertinggi di atas, ongoing, waiting di bawah
        return $data->sortWith(function ($a, $b) {
            $order = ['done' => 0, 'ongoing' => 1, 'waiting' => 2];
            $ao = $order[$a['status']] ?? 3;
            $bo = $order[$b['status']] ?? 3;

            if ($ao !== $bo) return $ao - $bo;
            if ($a['score'] !== $b['score']) return $b['score'] <=> $a['score'];
            return $a['time_spent'] <=> $b['time_spent'];
        })->values()->toArray();
    }

    /**
     * Leaderboard mode individu
     */
    private function getIndividualLeaderboard(CbtExam $exam): array
    {
        $results = CbtExamResult::where('exam_id', $exam->id)
            ->with(['student.user', 'session'])
            ->orderByDesc('final_score')
            ->orderBy('time_spent_seconds')
            ->get();

        return $results->map(function ($result, $index) {
            return [
                'rank'         => $index + 1,
                'display_name' => $result->student?->full_name ?? '-',
                'score'        => $result->final_score,
                'correct'      => $result->correct_answers,
                'wrong'        => $result->wrong_answers,
                'unanswered'   => $result->unanswered,
                'status'       => 'done',
                'time_spent'   => $result->time_spent_seconds,
            ];
        })->toArray();
    }
}
