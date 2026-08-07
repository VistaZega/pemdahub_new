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

        $this->info("Successfully synchronized {$count} alumni records across all tables.");
        return Command::SUCCESS;
    }
}
