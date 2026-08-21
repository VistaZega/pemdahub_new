<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\EducationalCalendar;
use App\Models\School;
use App\Services\EducationalCalendarService;
use Illuminate\Http\Request;

class EducationalCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(EducationalCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        if (!$academicYear) {
            return redirect()->back()->with('error', 'Tahun Ajaran aktif belum diatur.');
        }

        // Determine school scope
        if ($isSuperAdmin) {
            if ($request->filled('school_id') && $request->school_id !== 'all') {
                $school = School::find($request->school_id);
            } else {
                $school = null; // null means fetch ALL events across all school units!
            }
            $schools = School::schoolsOnly()->orderBy('name')->get();
        } else {
            $school = $user->school;
            $schools = collect($school ? [$school] : []);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($this->calendarService->getCalendarEvents($school, $academicYear));
        }

        $activeDaysGanjil = $this->calendarService->calculateActiveDaysForSemester($school, $academicYear, 1);
        $activeDaysGenap = $this->calendarService->calculateActiveDaysForSemester($school, $academicYear, 2);
        $activeDaysTotal = $this->calendarService->calculateActiveDays($school, $academicYear);

        return view('admin.calendar.index', compact('school', 'schools', 'academicYear', 'activeDaysGanjil', 'activeDaysGenap', 'activeDaysTotal', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',
            'is_holiday' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $schoolId = $isSuperAdmin ? null : $user->school_id;
        $level = $isSuperAdmin ? 'yayasan' : 'school';
        $type = $request->type;

        // Sesuaikan type jika SuperAdmin (karena form admin hanya punya school_event)
        if ($isSuperAdmin && $type === 'school_event') {
            $type = 'yayasan_event';
        }

        EducationalCalendar::create([
            'academic_year_id' => $academicYear->id,
            'school_id' => $schoolId,
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $type,
            'is_holiday' => $request->has('is_holiday') && $request->is_holiday == '1',
            'level' => $level,
            'created_by' => $user->id,
            'description' => $request->description,
        ]);

        $message = $isSuperAdmin ? 'Jadwal Yayasan berhasil ditambahkan.' : 'Jadwal Sekolah berhasil ditambahkan.';
        return redirect()->route('admin.calendar.index')->with('success', $message);
    }

    public function update(Request $request, EducationalCalendar $calendar)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        // Admin sekolah hanya bisa update event sekolah miliknya. Superadmin bisa update yayasan.
        if (!$isSuperAdmin && ($calendar->level !== 'school' || $calendar->school_id !== $user->school_id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah jadwal ini.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',
            'is_holiday' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $type = $request->type;
        if ($isSuperAdmin && $type === 'school_event' && $calendar->level === 'yayasan') {
            $type = 'yayasan_event';
        }

        $calendar->update([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $type,
            'is_holiday' => $request->has('is_holiday') && $request->is_holiday == '1',
            'description' => $request->description,
        ]);

        $message = $isSuperAdmin ? 'Jadwal Yayasan berhasil diperbarui.' : 'Jadwal Sekolah berhasil diperbarui.';
        return redirect()->route('admin.calendar.index')->with('success', $message);
    }

    public function print(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (!$academicYear) {
            return redirect()->back()->with('error', 'Tahun Ajaran aktif belum diatur.');
        }

        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $schoolId = $isSuperAdmin ? null : $user->school_id;

        $query = EducationalCalendar::where('academic_year_id', $academicYear->id)
            ->orderBy('start_date', 'asc');
            
        if (!$isSuperAdmin) {
            $query->where(function ($q) use ($schoolId) {
                $q->where('level', 'yayasan')
                  ->orWhere(function ($q2) use ($schoolId) {
                      $q2->where('level', 'school')
                         ->where('school_id', $schoolId);
                  });
            });
        }
        
        $events = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.calendar', compact('academicYear', 'events'))
                ->setPaper('a4', 'landscape');
        
        $safeYear = str_replace('/', '-', $academicYear->year);
        $filename = $isSuperAdmin ? 'Kalender_Pendidikan_Yayasan_' : 'Kalender_Pendidikan_Sekolah_';
        return $pdf->stream($filename . $safeYear . '.pdf');
    }

    public function destroy(EducationalCalendar $calendar)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        if (!$isSuperAdmin && ($calendar->level !== 'school' || $calendar->school_id !== $user->school_id)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus jadwal ini.');
        }

        $calendar->delete();

        $message = $isSuperAdmin ? 'Jadwal Yayasan berhasil dihapus.' : 'Jadwal Sekolah berhasil dihapus.';
        return redirect()->route('admin.calendar.index')->with('success', $message);
    }
}
