<?php


namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create schools under Yayasan Perguruan Pembda Nias
        $school1 = School::firstOrCreate(['type' => 'SMP'], [
            'name' => 'SMP Swasta Pembda 2 Gunungsitoli',
            'npsn' => '20220001',
            'address' => 'Jl. Pelita No.09',
            'city' => 'Gunungsitoli',
            'province' => 'Sumatera Utara',
            'postal_code' => '22812',
            'phone' => '082168532567',
            'email' => 'info@smp2pembda.sch.id',
            'website' => 'www.smp2pembda.sch.id',
            'principal_name' => 'Drs. Yusman Zega',
            'school_year_start' => 2024,
            'is_active' => true,
        ]);

        $school2 = School::firstOrCreate(['type' => 'SMA'], [
            'name' => 'SMA Swasta Pembda 1 Gunungsitoli',
            'npsn' => '20220002',
            'address' => 'Jl. Pelita No.09',
            'city' => 'Gunungsitoli',
            'province' => 'Sumatera Utara',
            'postal_code' => '22812',
            'phone' => '082168532567',
            'email' => 'info@sma1pembda.sch.id',
            'website' => 'www.sma1pembda.sch.id',
            'principal_name' => 'Drs. Faigiziduhu Zega',
            'school_year_start' => 2024,
            'is_active' => true,
        ]);

        $school3 = School::firstOrCreate(['type' => 'SMK'], [
            'name' => 'SMK Swasta Pembda Nias',
            'npsn' => '20220003',
            'address' => 'Jl. Pelita No.09',
            'city' => 'Gunungsitoli',
            'province' => 'Sumatera Utara',
            'postal_code' => '22812',
            'phone' => '082168532567',
            'email' => 'info@smkpembda.sch.id',
            'website' => 'www.smkpembda.sch.id',
            'principal_name' => 'Drs. Ama Zega',
            'school_year_start' => 2024,
            'is_active' => true,
        ]);

        // Create SuperAdmin
        User::firstOrCreate(['email' => 'superadmin@pembdahub.com'], [
            'name' => 'SuperAdmin',
            'password' => Hash::make('Superadmin@2026!'),
            'role' => 'superadmin',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Create Admin Sekolah for each school
        User::firstOrCreate(['email' => 'admin@smp2pembda.sch.id'], [
            'name' => 'Admin SMP Pembda 2',
            'password' => Hash::make('AdminSMP@2026!'),
            'role' => 'admin_sekolah',
            'school_id' => $school1->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        User::firstOrCreate(['email' => 'admin@sma1pembda.sch.id'], [
            'name' => 'Admin SMA Pembda 1',
            'password' => Hash::make('AdminSMA@2026!'),
            'role' => 'admin_sekolah',
            'school_id' => $school2->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        User::firstOrCreate(['email' => 'admin@smkpembda.sch.id'], [
            'name' => 'Admin SMK Pembda',
            'password' => Hash::make('AdminSMK@2026!'),
            'role' => 'admin_sekolah',
            'school_id' => $school3->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Create sample Guru
        User::firstOrCreate(['email' => 'ama.zega@sma1pembda.sch.id'], [
            'name' => 'Bapak Ama Zega',
            'password' => Hash::make('Guru@2026!'),
            'role' => 'guru',
            'school_id' => $school2->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Create sample Siswa
        User::firstOrCreate(['email' => 'ferdinan@student.smp2pembda.sch.id'], [
            'name' => 'Ferdinan Zega',
            'password' => Hash::make('Siswa@2026!'),
            'role' => 'siswa',
            'school_id' => $school1->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Create sample Orang Tua
        User::firstOrCreate(['email' => 'ama.ferdinan@parent.sch.id'], [
            'name' => 'Ama Ferdinan',
            'password' => Hash::make('OrangTua@2026!'),
            'role' => 'orang_tua',
            'school_id' => $school1->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        // Create Academic Years
        // Global academic years (school_id removed from table)
        AcademicYear::firstOrCreate([
            'year' => '2024/2025',
        ], [
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'semester_start' => '2024-07-01',
            'semester_end' => '2024-12-15',
            'is_active' => true,
        ]);

        // Seed some default majors per school if none exist
        if (\App\Models\Major::where('school_id', $school2->id)->count() === 0) {
            \App\Models\Major::create(['school_id' => $school2->id, 'major_code' => 'IPA', 'major_name' => 'Ilmu Pengetahuan Alam', 'description' => 'Jurusan IPA', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school2->id, 'major_code' => 'IPS', 'major_name' => 'Ilmu Pengetahuan Sosial', 'description' => 'Jurusan IPS', 'is_active' => true]);
        }

        if (\App\Models\Major::where('school_id', $school3->id)->count() === 0) {
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'TSM', 'major_name' => 'Teknik Sepeda Motor', 'description' => 'Jurusan TSM', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'TJKT', 'major_name' => 'Teknik Jaringan Komputer & Telekomunikasi', 'description' => 'Jurusan TJKT', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'TKR', 'major_name' => 'Teknik Kendaraan Ringan', 'description' => 'Jurusan TKR', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'TAV', 'major_name' => 'Teknik Audio Video', 'description' => 'Jurusan TAV', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'TE', 'major_name' => 'Teknik Elektronika', 'description' => 'Jurusan TE', 'is_active' => true]);
            \App\Models\Major::create(['school_id' => $school3->id, 'major_code' => 'DPIB', 'major_name' => 'Desain Pemodelan & Informasi Bangunan', 'description' => 'Jurusan DPIB', 'is_active' => true]);
        }

        // Call seeders for subjects and classrooms
        $this->call(SubjectSeeder::class);
        $this->call(ClassroomSeeder::class);

        $this->call(TeacherSeeder::class);
        $this->call(SubjectTeacherSeeder::class);
        $this->call(SemesterSeeder::class);
        $this->call(ScheduleSeeder::class);
        $this->call(ParentSeeder::class);

        // --- Tambah 5 siswa per kelas dan tempatkan ke student_classes ---
        $faker = \Faker\Factory::create('id_ID');
        $classrooms = Classroom::all();
        foreach ($classrooms as $classroom) {
            $schoolId = $classroom->school_id;
            $academicYearId = $classroom->academic_year_id;
            for ($i = 1; $i <= 5; $i++) {
                $user = User::create([
                    'name' => $faker->name('male'),
                    'email' => 'siswa' . $i . '_' . $classroom->id . '@student.sch.id',
                    'password' => Hash::make('Siswa@2026!'),
                    'role' => 'siswa',
                    'school_id' => $schoolId,
                    'is_active' => true,
                    'must_change_password' => true,
                ]);
                $student = Student::create([
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'nisn' => $faker->unique()->numerify('00########'),
                    'nis' => $faker->unique()->numerify('20######'),
                    'full_name' => $user->name,
                    'gender' => $faker->randomElement(['L', 'P']),
                    'birth_place' => $faker->city,
                    'birth_date' => $faker->date('Y-m-d', '2012-12-31'),
                    'religion' => 'Kristen',
                    'address' => $faker->address,
                    'phone' => $faker->phoneNumber,
                    'parent_name' => $faker->name('male'),
                    'parent_phone' => $faker->phoneNumber,
                    'entry_year' => 2024,
                    'status' => 'aktif',
                ]);
                StudentClass::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'academic_year_id' => $academicYearId,
                    'status' => 'aktif',
                ]);
            }
        }

        // Call LMS Seeder after students are created
        $this->call(LmsSeeder::class);

        // Seed landing page content (News & Gallery)
        $this->call(LandingPageContentSeeder::class);

        // Seed training modules
        $this->call(TrainingModuleSeeder::class);

        // Seed surveys
        $this->call(SurveySeeder::class);

        // Seed Tefa Employees
        $this->call(TefaEmployeeSeeder::class);

        // Untuk mengisi data simulasi lengkap (LMS, CBT, kehadiran, konseling, nilai & rapor),
        // jalankan command: php artisan db:seed --class=ComprehensiveSimulationSeeder
        // Atau hilangkan komentar pada baris di bawah ini jika ingin dijalankan secara default:
        // $this->call(ComprehensiveSimulationSeeder::class);

        $this->command->info('Database seeded successfully!');
    }
}
