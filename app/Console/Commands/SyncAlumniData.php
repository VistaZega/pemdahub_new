<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;

class SyncAlumniData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alumni:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all graduated students with Alumni, AlumniProfile, AlumniDirectory, and update User roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting alumni data synchronization...');

        $students = Student::whereIn('status', ['lulus', 'alumni'])->get();
        $count = 0;

        foreach ($students as $student) {
            $student->syncAlumniRecords();
            $count++;
        }

        // Auto-fix standalone records in `alumni` table where entry_year is invalid
        $standaloneAlumni = \App\Models\Alumni::all();
        foreach ($standaloneAlumni as $alumni) {
            $dirty = false;
            $gradYear = $alumni->graduation_year ?? now()->year;

            if (!$alumni->entry_year || $alumni->entry_year >= $gradYear || ($gradYear - $alumni->entry_year) < 2) {
                $alumni->entry_year = $gradYear - 3;
                $dirty = true;
            }

            if ($dirty) {
                $alumni->save();
            }
        }

        $this->info("Successfully synchronized {$count} alumni records and auto-corrected entry years across all tables.");
        return Command::SUCCESS;
    }
}
