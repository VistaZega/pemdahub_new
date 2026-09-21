<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Classroom;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Koreksi data absensi hari ini (dan seterusnya) yang berstatus 'hadir'
     * tetapi waktu time_in melebihi batas toleransi kelas sehingga seharusnya 'terlambat'.
     */
    public function up(): void
    {
        try {
            $today = '2026-09-21';
            $attendances = Attendance::where('date', '>=', $today)
                ->where('status', 'hadir')
                ->whereNotNull('time_in')
                ->get();

            $classrooms = Classroom::all()->keyBy('id');

            $updatedCount = 0;
            foreach ($attendances as $att) {
                $classroom = $classrooms->get($att->classroom_id);
                $lateThreshold = $classroom ? $classroom->getLateThreshold() : '07:45:00';

                $timeIn = $att->time_in;
                if (strlen($timeIn) === 5) {
                    $timeIn .= ':00';
                }

                if ($timeIn > $lateThreshold) {
                    $att->status = 'terlambat';
                    $att->save();
                    $updatedCount++;
                }
            }

            \Illuminate\Support\Facades\Log::info("Migration fix_late_attendances_today: {$updatedCount} record(s) updated to 'terlambat'.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migration fix_late_attendances_today notice: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
