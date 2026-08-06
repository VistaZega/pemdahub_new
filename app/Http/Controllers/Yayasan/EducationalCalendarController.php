<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\EducationalCalendar;
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
        $schools = \App\Models\School::where('is_active', true)->schoolsOnly()->get();
        
        if (!$academicYear) {
            return redirect()->back()->with('error', 'Tahun Ajaran aktif belum diatur.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            $schoolFilter = $request->get('school_id');
            $selectedSchool = ($schoolFilter && $schoolFilter !== 'all') ? \App\Models\School::find($schoolFilter) : null;
            return response()->json($this->calendarService->getCalendarEvents($selectedSchool, $academicYear));
        }

        $activeDaysGanjil = $this->calendarService->calculateActiveDaysForSemester(null, $academicYear, 1);
        $activeDaysGenap = $this->calendarService->calculateActiveDaysForSemester(null, $academicYear, 2);
        
        $activeDaysTotal = $this->calendarService->calculateActiveDays(null, $academicYear);

        return view('yayasan.calendar.index', compact('academicYear', 'schools', 'activeDaysGanjil', 'activeDaysGenap', 'activeDaysTotal'));
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
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $level = $request->filled('school_id') ? 'school' : 'yayasan';

        EducationalCalendar::create([
            'academic_year_id' => $academicYear->id,
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'is_holiday' => $request->has('is_holiday') && $request->is_holiday == '1',
            'level' => $level,
            'school_id' => $request->school_id,
            'created_by' => auth()->id(),
            'description' => $request->description,
        ]);

        return redirect()->route('yayasan.calendar.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, EducationalCalendar $calendar)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',
            'is_holiday' => 'boolean',
            'description' => 'nullable|string',
            'school_id' => 'nullable|exists:schools,id',
            'original_level' => 'nullable|string',
            'original_school_id' => 'nullable|integer',
        ]);

        // Gunakan original_level dari form hidden input untuk mempertahankan level asli event.
        // Jika event asalnya school-level, pertahankan school_id asli agar tidak berubah ke yayasan.
        $originalLevel = $request->input('original_level');
        if ($originalLevel === 'school') {
            // Event school-level: pertahankan level dan school_id asli
            $level    = 'school';
            $schoolId = $request->input('original_school_id') ?: $calendar->school_id;
        } else {
            // Event yayasan-level: tentukan dari field school_id form seperti biasa
            $level    = $request->filled('school_id') ? 'school' : 'yayasan';
            $schoolId = $request->filled('school_id') ? $request->school_id : null;
        }

        $calendar->update([
            'title'       => $request->title,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'type'        => $request->type,
            'is_holiday'  => $request->has('is_holiday') && $request->is_holiday == '1',
            'level'       => $level,
            'school_id'   => $schoolId,
            'description' => $request->description,
        ]);


        return redirect()->route('yayasan.calendar.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function print(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (!$academicYear) {
            return redirect()->back()->with('error', 'Tahun Ajaran aktif belum diatur.');
        }

        $query = EducationalCalendar::where('academic_year_id', $academicYear->id)
            ->orderBy('start_date', 'asc');
            
        // Yayasan gets all events, no need to filter by school_id
        $events = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.calendar', compact('academicYear', 'events'))
                ->setPaper('a4', 'landscape');
        
        $safeYear = str_replace('/', '-', $academicYear->year);
        return $pdf->stream('Kalender_Pendidikan_Yayasan_' . $safeYear . '.pdf');
    }

    public function printMondayInspiration()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat_edaran_monday_inspiration')
                ->setPaper('a4', 'portrait');
        
        return $pdf->stream('Surat_Edaran_Monday_Inspiration_TP_2026_2027.pdf');
    }

    public function destroy(EducationalCalendar $calendar)
    {
        $calendar->delete();

        return redirect()->route('yayasan.calendar.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}
