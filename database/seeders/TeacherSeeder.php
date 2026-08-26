<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::all();

        // Realistic teacher data per school type with Nias-relevant names
        $teacherData = [
            'SMP' => [
                ['name' => 'Samatutu Gulo', 'gender' => 'L', 'birth' => '1978-03-15', 'religion' => 'Kristen Protestan'],
                ['name' => 'Yarni Telaumbanua', 'gender' => 'P', 'birth' => '1982-07-22', 'religion' => 'Kristen Protestan'],
                ['name' => 'Firman Zebua', 'gender' => 'L', 'birth' => '1985-11-08', 'religion' => 'Kristen Protestan'],
                ['name' => 'Delima Waruwu', 'gender' => 'P', 'birth' => '1980-05-30', 'religion' => 'Islam'],
                ['name' => 'Arozatulo Lase', 'gender' => 'L', 'birth' => '1976-09-12', 'religion' => 'Kristen Protestan'],
                ['name' => 'Nurhaida Harefa', 'gender' => 'P', 'birth' => '1983-01-25', 'religion' => 'Islam'],
                ['name' => 'Talimadodo Zagoto', 'gender' => 'L', 'birth' => '1979-06-18', 'religion' => 'Kristen Protestan'],
                ['name' => 'Kristina Mendrofa', 'gender' => 'P', 'birth' => '1984-12-03', 'religion' => 'Kristen Katolik'],
            ],
            'SMA' => [
                ['name' => 'Ama Zega', 'gender' => 'L', 'birth' => '1975-04-10', 'religion' => 'Kristen Protestan'],
                ['name' => 'Roslina Giawa', 'gender' => 'P', 'birth' => '1981-08-14', 'religion' => 'Kristen Protestan'],
                ['name' => 'Hadirat Ndraha', 'gender' => 'L', 'birth' => '1977-02-28', 'religion' => 'Kristen Protestan'],
                ['name' => 'Yuliana Halawa', 'gender' => 'P', 'birth' => '1983-10-05', 'religion' => 'Islam'],
                ['name' => 'Bernadetha Duha', 'gender' => 'P', 'birth' => '1979-06-20', 'religion' => 'Kristen Katolik'],
                ['name' => 'Sadieli Hulu', 'gender' => 'L', 'birth' => '1982-03-17', 'religion' => 'Kristen Protestan'],
                ['name' => 'Fatolosa Laoli', 'gender' => 'L', 'birth' => '1976-11-09', 'religion' => 'Kristen Protestan'],
                ['name' => 'Serimawarni Baeha', 'gender' => 'P', 'birth' => '1984-07-30', 'religion' => 'Islam'],
            ],
            'SMK' => [
                ['name' => 'Noverius Telaumbanua', 'gender' => 'L', 'birth' => '1980-01-20', 'religion' => 'Kristen Protestan'],
                ['name' => 'Darlin Waruwu', 'gender' => 'P', 'birth' => '1983-05-12', 'religion' => 'Kristen Protestan'],
                ['name' => 'Yusman Gulo', 'gender' => 'L', 'birth' => '1978-09-25', 'religion' => 'Kristen Protestan'],
                ['name' => 'Hasian Zebua', 'gender' => 'P', 'birth' => '1985-03-08', 'religion' => 'Islam'],
                ['name' => 'Delisman Harefa', 'gender' => 'L', 'birth' => '1977-07-15', 'religion' => 'Kristen Protestan'],
                ['name' => 'Ratna Lase', 'gender' => 'P', 'birth' => '1982-11-30', 'religion' => 'Kristen Katolik'],
                ['name' => 'Fanotona Zagoto', 'gender' => 'L', 'birth' => '1979-04-22', 'religion' => 'Kristen Protestan'],
                ['name' => 'Elmiana Mendrofa', 'gender' => 'P', 'birth' => '1984-08-18', 'religion' => 'Kristen Protestan'],
            ],
        ];

        // Email domain mapping per school
        $emailDomains = [
            'SMP' => 'smp2pembda.sch.id',
            'SMA' => 'sma1pembda.sch.id',
            'SMK' => 'smkpembda.sch.id',
        ];

        foreach ($schools as $school) {
            $teachers = $teacherData[$school->type] ?? $teacherData['SMP'];
            $domain = $emailDomains[$school->type] ?? 'pembda.sch.id';

            foreach ($teachers as $index => $data) {
                // Generate email from name: "Noverius Telaumbanua" → "noveriustelaumbanua@smkpembda.sch.id"
                $emailName = strtolower(str_replace([' ', "'", '.'], '', $data['name']));
                $email = $emailName . '@' . $domain;

                // Create User for Teacher
                $user = User::firstOrCreate([
                    'email' => $email,
                ], [
                    'name' => $data['name'],
                    'password' => Hash::make('Guru@2026!'),
                    'role' => 'guru',
                    'school_id' => $school->id,
                    'is_active' => true,
                    'must_change_password' => true,
                ]);

                // Create Employee
                $employee = Employee::create([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'employee_code' => 'EMP' . str_pad($school->id * 100 + $index, 4, '0', STR_PAD_LEFT),
                    'full_name' => $data['name'],
                    'gender' => $data['gender'],
                    'employee_type' => 'guru',
                    'employment_status' => 'yayasan',
                    'tmt_date' => '2020-01-01',
                    'is_active' => true,
                ]);

                // Create Teacher
                Teacher::create([
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'teacher_code' => 'T' . str_pad($school->id * 100 + $index, 4, '0', STR_PAD_LEFT),
                    'full_name' => $data['name'],
                    'gender' => $data['gender'],
                    'birth_place' => 'Gunungsitoli',
                    'birth_date' => $data['birth'],
                    'religion' => $data['religion'],
                    'address' => 'Jl. Pendidikan No. ' . ($index + 1) . ', Gunungsitoli',
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'position' => 'Guru Tetap',
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Teachers seeded successfully!');
    }
}
