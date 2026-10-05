<?php

namespace App\Services;

use App\Models\LmsCourse;
use App\Models\LmsMaterial;
use App\Models\LmsMaterialProgress;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LmsProgressService
{
    /**
     * Calculate progress percentage (0 - 100) for a single course and student.
     * Unified comprehensive standard: Materi + Tugas + Kuis = 100%.
     * A course is only 100% when all existing materials, assignments, and quizzes are completed.
     *
     * @param int|LmsCourse $course
     * @param int $studentId
     * @return int (0-100)
     */
    public function calculateCourseProgress(int|LmsCourse $course, int $studentId): int
    {
        $details = $this->getDetailedCourseProgress($course, $studentId);
        return $details['overall'] ?? 0;
    }

    /**
     * Get detailed breakdown of course progress (materi, tugas, kuis).
     *
     * @param int|LmsCourse $course
     * @param int $studentId
     * @return array
     */
    public function getDetailedCourseProgress(int|LmsCourse $course, int $studentId): array
    {
        $courseId = $course instanceof LmsCourse ? $course->id : (int) $course;

        // 1. Published Materials
        $materialIds = LmsMaterial::where('course_id', $courseId)
            ->where('is_published', true)
            ->pluck('id')
            ->toArray();
        $totalMats = count($materialIds);
        $completedMats = 0;
        if ($totalMats > 0) {
            $completedMats = LmsMaterialProgress::where('student_id', $studentId)
                ->whereIn('material_id', $materialIds)
                ->where('status', 'completed')
                ->distinct('material_id')
                ->count('material_id');
        }

        // 2. Published Assignments
        $assignmentIds = LmsAssignment::where('course_id', $courseId)
            ->where('is_published', true)
            ->pluck('id')
            ->toArray();
        $totalAssigns = count($assignmentIds);
        $submittedAssigns = 0;
        if ($totalAssigns > 0) {
            // Individual submissions
            $individualSubmitted = LmsSubmission::where('student_id', $studentId)
                ->whereIn('assignment_id', $assignmentIds)
                ->whereIn('status', ['submitted', 'graded', 'late', 'revision_requested'])
                ->pluck('assignment_id')
                ->toArray();

            // Group assignments (leader or member)
            $groupSubmitted = DB::table('lms_submissions as s')
                ->join('lms_assignment_groups as g', 's.group_id', '=', 'g.id')
                ->leftJoin('lms_assignment_group_members as gm', 'g.id', '=', 'gm.group_id')
                ->whereIn('s.assignment_id', $assignmentIds)
                ->whereIn('s.status', ['submitted', 'graded', 'late', 'revision_requested'])
                ->where(function ($q) use ($studentId) {
                    $q->where('g.leader_id', $studentId)
                      ->orWhere('gm.student_id', $studentId);
                })
                ->pluck('s.assignment_id')
                ->toArray();

            $submittedAssigns = count(array_unique(array_merge($individualSubmitted, $groupSubmitted)));
        }

        // 3. Published Quizzes
        $quizIds = LmsQuiz::where('course_id', $courseId)
            ->where('is_published', true)
            ->pluck('id')
            ->toArray();
        $totalQuizzes = count($quizIds);
        $completedQuizzes = 0;
        if ($totalQuizzes > 0) {
            $completedQuizzes = LmsQuizAttempt::where('student_id', $studentId)
                ->whereIn('quiz_id', $quizIds)
                ->whereNotNull('finished_at')
                ->distinct('quiz_id')
                ->count('quiz_id');
        }

        return $this->computeProgressBreakdown($totalMats, $completedMats, $totalAssigns, $submittedAssigns, $totalQuizzes, $completedQuizzes);
    }

    /**
     * Core progress formula:
     * Evaluates existing components (Materi, Tugas, Kuis).
     * If all 3 components exist: overall = (matPct + assignPct + quizPct) / 3.
     * If only 2 components exist: overall = sumPct / 2.
     * If only 1 component exists: overall = matPct (or assignPct / quizPct).
     * If no components exist: 0%.
     *
     * @param int $totalMats
     * @param int $completedMats
     * @param int $totalAssigns
     * @param int $submittedAssigns
     * @param int $totalQuizzes
     * @param int $completedQuizzes
     * @return array
     */
    public function computeProgressBreakdown(
        int $totalMats,
        int $completedMats,
        int $totalAssigns,
        int $submittedAssigns,
        int $totalQuizzes,
        int $completedQuizzes
    ): array {
        $itemsCount = 0;
        $sumPct = 0;

        $matPct = $totalMats > 0 ? (int) min(100, round(($completedMats / $totalMats) * 100)) : 0;
        if ($totalMats > 0) {
            $sumPct += $matPct;
            $itemsCount++;
        }

        $assignPct = $totalAssigns > 0 ? (int) min(100, round(($submittedAssigns / $totalAssigns) * 100)) : 0;
        if ($totalAssigns > 0) {
            $sumPct += $assignPct;
            $itemsCount++;
        }

        $quizPct = $totalQuizzes > 0 ? (int) min(100, round(($completedQuizzes / $totalQuizzes) * 100)) : 0;
        if ($totalQuizzes > 0) {
            $sumPct += $quizPct;
            $itemsCount++;
        }

        $overallPct = $itemsCount > 0 ? (int) min(100, max(0, round($sumPct / $itemsCount))) : 0;

        return [
            'overall' => $overallPct,
            'materials' => [
                'total' => $totalMats,
                'completed' => min($totalMats, $completedMats),
                'percent' => $matPct,
            ],
            'assignments' => [
                'total' => $totalAssigns,
                'completed' => min($totalAssigns, $submittedAssigns),
                'percent' => $assignPct,
            ],
            'quizzes' => [
                'total' => $totalQuizzes,
                'completed' => min($totalQuizzes, $completedQuizzes),
                'percent' => $quizPct,
            ],
            'has_materials' => $totalMats > 0,
            'has_assignments' => $totalAssigns > 0,
            'has_quizzes' => $totalQuizzes > 0,
            'items_count' => $itemsCount,
        ];
    }

    /**
     * Batch calculate progress percentages for multiple courses for a student.
     * Prevents N+1 queries.
     *
     * @param iterable|Collection $courses
     * @param int $studentId
     * @return array [course_id => overall_pct, ...]
     */
    public function calculateMultipleCoursesProgressForStudent($courses, int $studentId): array
    {
        $details = $this->getMultipleCoursesProgressDetailsForStudent($courses, $studentId);
        $result = [];
        foreach ($details as $courseId => $item) {
            $result[$courseId] = $item['overall'] ?? 0;
        }
        return $result;
    }

    /**
     * Batch calculate full progress details for multiple courses for a student.
     *
     * @param iterable|Collection $courses
     * @param int $studentId
     * @return array [course_id => breakdown_array, ...]
     */
    public function getMultipleCoursesProgressDetailsForStudent($courses, int $studentId): array
    {
        $courseList = collect($courses);
        if ($courseList->isEmpty()) {
            return [];
        }

        $courseIds = $courseList->map(function ($c) {
            return $c instanceof LmsCourse ? $c->id : (is_numeric($c) ? (int) $c : null);
        })->filter()->unique()->values()->toArray();

        if (empty($courseIds)) {
            return [];
        }

        // 1. Fetch published materials, assignments, quizzes grouped by course_id
        $materials = LmsMaterial::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->select('id', 'course_id')
            ->get();
        $allMaterialIds = $materials->pluck('id')->toArray();
        $courseMaterialMap = $materials->groupBy('course_id');

        $assignments = LmsAssignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->select('id', 'course_id')
            ->get();
        $allAssignmentIds = $assignments->pluck('id')->toArray();
        $courseAssignmentMap = $assignments->groupBy('course_id');

        $quizzes = LmsQuiz::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->select('id', 'course_id')
            ->get();
        $allQuizIds = $quizzes->pluck('id')->toArray();
        $courseQuizMap = $quizzes->groupBy('course_id');

        // 2. Fetch student completed materials
        $completedMaterialLookup = [];
        if (!empty($allMaterialIds)) {
            $completedMaterialIds = LmsMaterialProgress::where('student_id', $studentId)
                ->whereIn('material_id', $allMaterialIds)
                ->where('status', 'completed')
                ->pluck('material_id')
                ->unique()
                ->toArray();
            $completedMaterialLookup = array_flip($completedMaterialIds);
        }

        // 3. Fetch student submitted assignments (individual + group)
        $submittedAssignmentLookup = [];
        if (!empty($allAssignmentIds)) {
            $individualSubmitted = LmsSubmission::where('student_id', $studentId)
                ->whereIn('assignment_id', $allAssignmentIds)
                ->whereIn('status', ['submitted', 'graded', 'late', 'revision_requested'])
                ->pluck('assignment_id')
                ->toArray();

            $groupSubmitted = DB::table('lms_submissions as s')
                ->join('lms_assignment_groups as g', 's.group_id', '=', 'g.id')
                ->leftJoin('lms_assignment_group_members as gm', 'g.id', '=', 'gm.group_id')
                ->whereIn('s.assignment_id', $allAssignmentIds)
                ->whereIn('s.status', ['submitted', 'graded', 'late', 'revision_requested'])
                ->where(function ($q) use ($studentId) {
                    $q->where('g.leader_id', $studentId)
                      ->orWhere('gm.student_id', $studentId);
                })
                ->pluck('s.assignment_id')
                ->toArray();

            $submittedAssignmentIds = array_unique(array_merge($individualSubmitted, $groupSubmitted));
            $submittedAssignmentLookup = array_flip($submittedAssignmentIds);
        }

        // 4. Fetch student completed quizzes
        $completedQuizLookup = [];
        if (!empty($allQuizIds)) {
            $completedQuizIds = LmsQuizAttempt::where('student_id', $studentId)
                ->whereIn('quiz_id', $allQuizIds)
                ->whereNotNull('finished_at')
                ->pluck('quiz_id')
                ->unique()
                ->toArray();
            $completedQuizLookup = array_flip($completedQuizIds);
        }

        // 5. Compute breakdown for each course
        $results = [];
        foreach ($courseIds as $cId) {
            $cMats = $courseMaterialMap->get($cId, collect());
            $cAssigns = $courseAssignmentMap->get($cId, collect());
            $cQuizzes = $courseQuizMap->get($cId, collect());

            $totalMats = $cMats->count();
            $completedMats = $cMats->filter(fn($m) => isset($completedMaterialLookup[$m->id]))->count();

            $totalAssigns = $cAssigns->count();
            $submittedAssigns = $cAssigns->filter(fn($a) => isset($submittedAssignmentLookup[$a->id]))->count();

            $totalQuizzes = $cQuizzes->count();
            $completedQuizzes = $cQuizzes->filter(fn($q) => isset($completedQuizLookup[$q->id]))->count();

            $results[$cId] = $this->computeProgressBreakdown($totalMats, $completedMats, $totalAssigns, $submittedAssigns, $totalQuizzes, $completedQuizzes);
        }

        return $results;
    }
}
