<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\EducationalCalendar;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EducationalCalendarService
{
    /**
     * Calculate the total active days for a given school and academic year.
     * Skips Saturdays, Sundays, and any dates marked as 'is_holiday' = true
     * either at the yayasan level or the specific school level.
     */
    public function calculateActiveDays(?School $school, AcademicYear $academicYear): int
    {
        $startDate = Carbon::parse($academicYear->start_date);
        $endDate = Carbon::parse($academicYear->end_date);

        return $this->calculateActiveDaysBetween($school, $academicYear, $startDate, $endDate);
    }

    /**
     * Calculate active days for a specific semester (Ganjil=1, Genap=2), prioritizing the semesters table.
     */
    public function calculateActiveDaysForSemester(?School $school, AcademicYear $academicYear, int $semesterNumber): int
    {
        $semester = $academicYear->semesters()->where('semester_number', $semesterNumber)->first();

        if ($semester && $semester->start_date && $semester->end_date) {
            $start = $semester->start_date;
            $end = $semester->end_date;
        } else {
            if ($semesterNumber === 1) {
                $start = $academicYear->semester_start ?: $academicYear->start_date;
                $end = $academicYear->semester_end ?: \Carbon\Carbon::parse($academicYear->start_date)->endOfYear()->format('Y-m-d');
            } else {
                $start = $academicYear->semester_end ? \Carbon\Carbon::parse($academicYear->semester_end)->addDay()->format('Y-m-d') : \Carbon\Carbon::parse($academicYear->end_date)->startOfYear()->format('Y-m-d');
                $end = $academicYear->end_date;
            }
        }

        return $this->calculateActiveDaysBetween($school, $academicYear, $start, $end);
    }

    /**
     * Calculate active days between two specific dates.
     */
    public function calculateActiveDaysBetween(?School $school, AcademicYear $academicYear, $startDate, $endDate): int
    {
        $start = ($startDate instanceof Carbon) ? $startDate->copy() : Carbon::parse($startDate);
        $end = ($endDate instanceof Carbon) ? $endDate->copy() : Carbon::parse($endDate);

        if ($start->gt($end)) {
            return 0;
        }

        // We use a dynamic cache key based on dates
        $schoolId = $school ? $school->id : 'yayasan';
        $cacheKey = "active_days_between_{$schoolId}_{$academicYear->id}_{$start->format('Y-m-d')}_{$end->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($school, $academicYear, $start, $end) {
            // Get all holiday events for this period
            $query = EducationalCalendar::where('academic_year_id', $academicYear->id)
                ->where('is_holiday', true);
                
            if ($school) {
                $query->where(function ($q) use ($school) {
                    $q->where('level', 'yayasan')
                      ->orWhere(function ($q2) use ($school) {
                          $q2->where('level', 'school')
                            ->where('school_id', $school->id);
                      });
                });
            } else {
                $query->where('level', 'yayasan');
            }

            $holidayDates = $query->get()
                ->flatMap(function ($event) {
                    $dates = [];
                    $evtStart = Carbon::parse($event->start_date);
                    $evtEnd = Carbon::parse($event->end_date);
                    
                    while ($evtStart->lte($evtEnd)) {
                        $dates[] = $evtStart->format('Y-m-d');
                        $evtStart->addDay();
                    }
                    return $dates;
                })
                ->unique()
                ->toArray();

            $activeDaysCount = 0;
            $currentDate = $start->copy();

            while ($currentDate->lte($end)) {
                // Check if it's Saturday (6) or Sunday (0)
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // Check if it's in the holiday dates array
                if (in_array($currentDate->format('Y-m-d'), $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                $activeDaysCount++;
                $currentDate->addDay();
            }

            return $activeDaysCount;
        });
    }

    /**
     * Get all calendar events for a school and academic year.
     * This is used to render the FullCalendar UI.
     */
    public function getCalendarEvents(?School $school, AcademicYear $academicYear)
    {
        $query = EducationalCalendar::where('academic_year_id', $academicYear->id);

        if ($school) {
            $query->where(function ($q) use ($school) {
                $q->where('level', 'yayasan')
                  ->orWhere(function ($q2) use ($school) {
                      $q2->where('level', 'school')
                         ->where('school_id', $school->id);
                  });
            });
        } else {
            // If no school is provided (Yayasan Dashboard), get ALL events (Yayasan + all schools)
            // No additional where clause needed, the query will return everything for this academic year
        }

        return $query->with('school')->get()->map(function ($event) {
            $title = $event->title;
            if ($event->level === 'school' && $event->school) {
                $title = '[' . $event->school->type . '] ' . $title;
            }

            return [
                'id' => $event->id,
                'title' => $title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->copy()->addDay()->format('Y-m-d'), // FullCalendar exclusive end date
                'allDay' => true,
                'backgroundColor' => $this->getEventColor($event),
                'borderColor' => $this->getEventColor($event),
                'extendedProps' => [
                    'original_title' => $event->title,
                    'type' => $event->type,
                    'is_holiday' => $event->is_holiday,
                    'level' => $event->level,
                    'school_id' => $event->school_id,
                    'description' => $event->description,
                ]
            ];
        });
    }

    private function getEventColor(EducationalCalendar $event): string
    {
        // Warna khusus Monday Inspiration (ungu/purple)
        if ($event->type === 'monday_inspiration') {
            return '#8b5cf6';
        }

        if ($event->level === 'yayasan') {
            return $event->is_holiday ? '#e11d48' : '#f97316'; // Red for yayasan holiday, Orange for yayasan event
        }
        
        if ($event->school) {
            $typeKey = strtoupper($event->school->type ?? '');
            if ($typeKey === 'SMK') {
                return $event->is_holiday ? '#4f46e5' : '#059669'; // Indigo for SMK holiday, Emerald for SMK event
            } elseif ($typeKey === 'SMA') {
                return $event->is_holiday ? '#d97706' : '#2563eb'; // Amber for SMA holiday, Blue for SMA event
            } elseif ($typeKey === 'SMP') {
                return $event->is_holiday ? '#db2777' : '#0891b2'; // Pink for SMP holiday, Cyan for SMP event
            } elseif ($typeKey === 'SD') {
                return $event->is_holiday ? '#9333ea' : '#ea580c'; // Purple for SD holiday, Orange for SD event
            }
        }

        return $event->is_holiday ? '#4f46e5' : '#059669';
    }

    public function clearCache(School $school, AcademicYear $academicYear)
    {
        $cacheKey = "active_days_{$school->id}_{$academicYear->id}";
        Cache::forget($cacheKey);
    }
}
