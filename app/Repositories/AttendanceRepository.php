<?php

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository
{
    /**
     * Get paginated attendances with relationships
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Attendance::with([
            'student:id,full_name,nisn,school_id,photo',
            'classroom:id,class_name,grade_level'
        ])
            ->select('id', 'student_id', 'classroom_id', 'date', 'time_in', 'time_out', 'status', 'recorded_via', 'notes', 'attachment');

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->whereHas('classroom', function($q) use ($filters) {
                $q->where('academic_year_id', $filters['academic_year_id']);
            });
        }

        if (!empty($filters['school_id'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Tampilkan hanya kehadiran fisik secara default (hadir & terlambat) agar terhindar dari data massal (sakit, izin, alpa, libur)
            $query->whereIn('status', ['hadir', 'terlambat']);
        }

        if (!empty($filters['exclude_manual'])) {
            $query->whereIn('recorded_via', ['rfid', 'qr_gps', 'face_recognition']);
        }

        return $query->orderBy('date', 'desc')
            ->orderByRaw("GREATEST(COALESCE(time_in, '00:00:00'), COALESCE(time_out, '00:00:00')) DESC")
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get attendances by date range
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $classroomId = null): Collection
    {
        $query = Attendance::with(['student:id,full_name,nisn'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        return $query->orderBy('date')->orderBy('student_id')->get();
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics(int $studentId, ?int $classroomId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Attendance::where('student_id', $studentId);

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $attendances = $query->get();
        // Deduplikasi per tanggal agar multiple input mapel tidak melipatgandakan hari hadir siswa
        $dateGroups = $attendances->groupBy('date');
        $present = $dateGroups->filter(fn($recs) => $recs->contains(fn($r) => in_array($r->status, ['hadir', 'terlambat'])))->count();
        $izin = $dateGroups->filter(fn($recs) => $recs->every(fn($r) => $r->status === 'izin'))->count();
        $sakit = $dateGroups->filter(fn($recs) => $recs->every(fn($r) => $r->status === 'sakit'))->count();
        $alpha = $dateGroups->filter(fn($recs) => $recs->every(fn($r) => $r->status === 'alpha'))->count();

        $statisticsService = new \App\Services\AttendanceStatisticsService();
        $targetDate = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        
        // Find relevant academic year for start date
        $ay = \App\Models\AcademicYear::where('is_active', true)->first();
        $calcStart = $startDate ? \Carbon\Carbon::parse($startDate) : ($ay ? $ay->start_date : $targetDate->copy()->startOfMonth());
        if ($calcStart->gt($targetDate)) {
            $calcStart = $targetDate;
        }
        
        $z = $statisticsService->calculateZ($calcStart, $targetDate, $classroomId);

        return [
            'total' => $dateGroups->count(),
            'hadir' => $present,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'z_days' => $z,
            'presence_rate' => ($z > 0) ? round(min(100, ($present / $z) * 100), 1) : 0,
        ];
    }

    /**
     * Create attendance
     */
    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    /**
     * Update attendance
     */
    public function update(Attendance $attendance, array $data): bool
    {
        return $attendance->update($data);
    }

    /**
     * Delete attendance
     */
    public function delete(Attendance $attendance): bool
    {
        return $attendance->delete();
    }

    /**
     * Check if attendance exists
     */
    public function exists(int $studentId, int $classroomId, string $date, ?int $excludeId = null): bool
    {
        $query = Attendance::where('student_id', $studentId)
            ->where('classroom_id', $classroomId)
            ->where('date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
