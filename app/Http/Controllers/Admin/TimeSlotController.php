<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\TimeSlot;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get schools
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->where('type', '!=', 'yayasan')->get()
            : School::where('id', $user->school_id)->where('type', '!=', 'yayasan')->get();
        
        if ($user->isSuperAdmin()) {
            $selectedSchoolId = $request->filled('school_id')
                ? $request->school_id
                : ($schools->first() ? $schools->first()->id : null);
        } else {
            $selectedSchoolId = $user->school_id;
        }
        
        $selectedDay = $request->get('day', 'monday');
        
        $activeYear = AcademicYear::where('is_active', 1)->first();
        $selectedYearId = $request->get('academic_year_id', $activeYear ? $activeYear->id : null);
        
        // Get time slots for selected school, day, and academic year
        $timeSlots = TimeSlot::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('day_of_week', $selectedDay)
            ->orderBy('slot_order')
            ->get();
        
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu'
        ];
        
        return view('admin.time-slots.index', compact(
            'schools',
            'timeSlots',
            'selectedSchoolId',
            'selectedYearId',
            'selectedDay',
            'days'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->where('type', '!=', 'yayasan')->get()
            : School::where('id', $user->school_id)->where('type', '!=', 'yayasan')->get();
        
        if ($user->isSuperAdmin()) {
            $selectedSchoolId = $request->get('school_id', $schools->first() ? $schools->first()->id : null);
        } else {
            $selectedSchoolId = $request->get('school_id', $user->school_id);
        }
        $selectedDay = $request->get('day', 'monday');
        
        $activeYear = AcademicYear::where('is_active', 1)->first();
        $selectedYearId = $request->get('academic_year_id', $activeYear ? $activeYear->id : null);
        
        // Get next slot order
        $maxOrder = TimeSlot::where('school_id', $selectedSchoolId)
            ->where('academic_year_id', $selectedYearId)
            ->where('day_of_week', $selectedDay)
            ->max('slot_order') ?? 0;
        
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu'
        ];
        
        return view('admin.time-slots.create', compact(
            'schools',
            'selectedSchoolId',
            'selectedYearId',
            'selectedDay',
            'maxOrder',
            'days'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'slot_name' => 'required|string|max:50',
            'shift' => 'nullable|in:pagi,siang,all',
            'slot_type' => 'required|in:lesson,break,ceremony',
            'slot_order' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_teaching_slot' => 'required|boolean',
        ]);
        
        $validated['shift'] = $request->input('shift', 'pagi');
        
        // Check authorization
        if (!$user->isSuperAdmin() && $validated['school_id'] != $user->school_id) {
            abort(403);
        }
        
        // Calculate duration
        $start = \Carbon\Carbon::parse($validated['start_time']);
        $end = \Carbon\Carbon::parse($validated['end_time']);
        $validated['duration_minutes'] = $end->diffInMinutes($start);
        
        // Check for overlapping time slots within the same shift
        $shift = $validated['shift'] ?? 'pagi';
        $overlapSlot = TimeSlot::where('school_id', $validated['school_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function($q) use ($shift) {
                $q->where('shift', $shift)->orWhere('shift', 'all')->orWhereNull('shift');
            })
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->first();
        
        if ($overlapSlot) {
            $timeStr = \Carbon\Carbon::parse($overlapSlot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($overlapSlot->end_time)->format('H:i');
            return back()->withErrors(['start_time' => 'Waktu bertumpuk dengan time slot lain: ' . $overlapSlot->slot_name . ' (' . $timeStr . ')'])->withInput();
        }
        
        TimeSlot::create($validated);
        
        // Clear cached time slots for this school and year
        cache()->forget("timeslots_school_{$validated['school_id']}_year_{$validated['academic_year_id']}");
        
        return redirect()
            ->route('admin.time-slots.index', [
                'school_id' => $validated['school_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'day' => $validated['day_of_week']
            ])
            ->with('success', 'Time slot berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeSlot $timeSlot)
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && $timeSlot->school_id != $user->school_id) {
            abort(403);
        }
        
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->where('type', '!=', 'yayasan')->get()
            : School::where('id', $user->school_id)->where('type', '!=', 'yayasan')->get();
        
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu'
        ];
        
        return view('admin.time-slots.edit', compact('timeSlot', 'schools', 'days'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeSlot $timeSlot)
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && $timeSlot->school_id != $user->school_id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'slot_name' => 'required|string|max:50',
            'shift' => 'nullable|in:pagi,siang,all',
            'slot_type' => 'required|in:lesson,break,ceremony',
            'slot_order' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_teaching_slot' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);
        
        $validated['shift'] = $request->input('shift', 'pagi');
        
        // Calculate duration
        $start = \Carbon\Carbon::parse($validated['start_time']);
        $end = \Carbon\Carbon::parse($validated['end_time']);
        $validated['duration_minutes'] = $end->diffInMinutes($start);
        
        // Check for overlapping time slots within the same shift (exclude current)
        $shift = $validated['shift'] ?? 'pagi';
        $overlapSlot = TimeSlot::where('school_id', $validated['school_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('id', '!=', $timeSlot->id)
            ->where(function($q) use ($shift) {
                $q->where('shift', $shift)->orWhere('shift', 'all')->orWhereNull('shift');
            })
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->first();
        
        if ($overlapSlot) {
            $timeStr = \Carbon\Carbon::parse($overlapSlot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($overlapSlot->end_time)->format('H:i');
            return back()->withErrors(['start_time' => 'Waktu bertumpuk dengan time slot lain: ' . $overlapSlot->slot_name . ' (' . $timeStr . ')'])->withInput();
        }
        
        $timeSlot->update($validated);
        
        // Clear cached time slots for this school and year
        cache()->forget("timeslots_school_{$validated['school_id']}_year_{$validated['academic_year_id']}");
        
        return redirect()
            ->route('admin.time-slots.index', [
                'school_id' => $validated['school_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'day' => $validated['day_of_week']
            ])
            ->with('success', 'Time slot berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $timeSlot)
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && $timeSlot->school_id != $user->school_id) {
            abort(403);
        }
        
        // Check if time slot is used in schedules
        if ($timeSlot->schedules()->exists()) {
            return back()->withErrors(['error' => 'Time slot tidak bisa dihapus karena sedang digunakan dalam jadwal']);
        }
        
        $schoolId = $timeSlot->school_id;
        $academicYearId = $timeSlot->academic_year_id;
        $day = $timeSlot->day_of_week;
        
        $timeSlot->delete();
        
        // Clear cached time slots for this school and year
        cache()->forget("timeslots_school_{$schoolId}_year_{$academicYearId}");
        
        return redirect()
            ->route('admin.time-slots.index', [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'day' => $day
            ])
            ->with('success', 'Time slot berhasil dihapus');
    }
}
