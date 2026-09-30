<?php

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository
{
    /**
     * Get filtered and paginated students
     */
    public function getFilteredPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $user = auth()->user();
        $query = Student::query();

        // Auto-filter by school_id for non-superadmin users
        if ($user && !$user->isSuperAdmin()) {
            $userSchoolId = $user->getActiveSchoolId() ?? $user->school_id ?? $user->teacher?->school_id;
            if ($userSchoolId) {
                $query->where('school_id', $userSchoolId);
            }
        }

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['q']}%")
                    ->orWhere('nisn', 'like', "%{$filters['q']}%")
                    ->orWhere('nis', 'like', "%{$filters['q']}%");
            });
        }

        // Allow manual school_id filter for superadmin
        if (!empty($filters['school_id']) && $user && $user->isSuperAdmin()) {
            $query->where('school_id', $filters['school_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['classroom_id'])) {
            $query->whereHas('studentClasses', function ($q) use ($filters) {
                $q->where('classroom_id', $filters['classroom_id'])
                    ->where('status', 'aktif');
                // Also filter by academic year if provided, to handle same-named classes in different years
                if (!empty($filters['academic_year_id'])) {
                    $q->where('academic_year_id', $filters['academic_year_id']);
                }
            });
        } elseif (!empty($filters['academic_year_id'])) {
            // Filter by academic year through student_classes relationship
            // Tampilkan juga siswa baru yang belum pernah masuk kelas mana pun (orWhereDoesntHave)
            $query->where(function ($q) use ($filters) {
                $q->whereHas('studentClasses', function ($q2) use ($filters) {
                    $q2->where('academic_year_id', $filters['academic_year_id'])
                       ->where('status', 'aktif');
                })->orWhereDoesntHave('studentClasses');
            });
        }

        return $query->with(['school:id,name', 'currentClassroom'])
            ->orderBy('full_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get student by ID with relationships
     */
    public function findWithRelations(int $id): ?Student
    {
        return Student::with(['school', 'user'])->find($id);
    }

    /**
     * Get active students count by school
     */
    public function getActiveCount(?int $schoolId = null): int
    {
        $user = auth()->user();
        $query = Student::where('status', 'aktif');

        // Auto-filter for non-superadmin
        if ($user && !$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        } elseif ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return $query->count();
    }

    /**
     * Get all active students
     */
    public function getActive(): Collection
    {
        $user = auth()->user();
        $query = Student::where('status', 'aktif');

        // Auto-filter for non-superadmin
        if ($user && !$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        return $query->orderBy('full_name')->get();
    }

    /**
     * Create student
     */
    public function create(array $data): Student
    {
        return Student::create($data);
    }

    /**
     * Update student
     */
    public function update(Student $student, array $data): bool
    {
        return $student->update($data);
    }

    /**
     * Delete student
     */
    public function delete(Student $student): bool
    {
        return $student->delete();
    }

    /**
     * Check if NISN exists
     */
    public function nisnExists(string $nisn, ?int $excludeId = null): bool
    {
        $query = Student::where('nisn', $nisn);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
