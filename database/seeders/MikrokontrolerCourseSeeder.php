<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\LmsModule;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use App\Models\Classroom;

class MikrokontrolerCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * DEPRECATED: Seeder ini dinonaktifkan permanen karena sebelumnya membuat dummy course 
     * dan kelas X TAV berulang kali yang menghantui akun guru Yulianus Zega pada setiap deployment.
     */
    public function run(): void
    {
        // Bersihkan jika masih ada record dummy tersisa
        $dummyCourses = LmsCourse::withTrashed()
            ->where(function ($q) {
                $q->where('code', 'LIKE', 'LMS-MIKRO-XTAV%')
                  ->orWhere('course_name', 'Bahasa Pemrograman Mikrokontroler');
            })
            ->get();

        foreach ($dummyCourses as $dc) {
            LmsClass::where('course_id', $dc->id)->delete();
            LmsModule::where('course_id', $dc->id)->forceDelete();
            LmsAssignment::where('course_id', $dc->id)->forceDelete();
            LmsQuiz::where('course_id', $dc->id)->forceDelete();
            $dc->forceDelete();
        }

        // Hapus relasi lms_classes untuk X TAV jika ada
        $xtavClass = Classroom::where('class_name', 'X TAV')->first();
        if ($xtavClass) {
            LmsClass::where('classroom_id', $xtavClass->id)->delete();
        }
    }
}
