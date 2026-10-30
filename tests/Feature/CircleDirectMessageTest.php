<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Message;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircleDirectMessageTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $academicYear;
    protected $studentUser;
    protected $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
        ]);

        $this->school = School::firstOrCreate(
            ['slug' => 'smk-pembda'],
            ['name' => 'SMK Swasta Pembda', 'type' => 'smk', 'is_active' => true]
        );

        $classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'X-TKJ-1',
            'class_name' => 'X TKJ 1',
            'grade_level' => 10,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
        ]);

        Student::create([
            'user_id' => $this->studentUser->id,
            'school_id' => $this->school->id,
            'classroom_id' => $classroom->id,
            'full_name' => 'Budi Santoso',
            'nis' => '12345',
            'nisn' => '0012345678',
            'entry_year' => 2026,
            'status' => 'aktif',
            'gender' => 'L',
        ]);

        $this->teacherUser = User::factory()->create([
            'role' => 'guru',
            'name' => 'Pak Guru Hebat',
            'username' => 'guru_hebat',
        ]);

        Teacher::create([
            'user_id' => $this->teacherUser->id,
            'school_id' => $this->school->id,
            'teacher_code' => 'T-001',
            'full_name' => 'Pak Guru Hebat',
            'nip' => '1987654321',
            'title_ahead' => '',
            'title_behind' => 'S.Kom',
            'status' => 'aktif',
            'gender' => 'L',
        ]);
    }

    public function test_can_send_direct_message()
    {
        $response = $this->actingAs($this->studentUser)
            ->postJson(route('circle.messages.send'), [
                'receiver_id' => $this->teacherUser->id,
                'message' => 'Selamat siang Pak, mau tanya tentang tugas project PembdaCIRCLE!',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'is_me' => true,
                    'message' => 'Selamat siang Pak, mau tanya tentang tugas project PembdaCIRCLE!',
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->studentUser->id,
            'receiver_id' => $this->teacherUser->id,
            'message' => 'Selamat siang Pak, mau tanya tentang tugas project PembdaCIRCLE!',
            'is_read' => false,
        ]);
    }

    public function test_can_get_inbox_and_conversation()
    {
        Message::create([
            'sender_id' => $this->teacherUser->id,
            'receiver_id' => $this->studentUser->id,
            'message' => 'Halo Budi, silakan kumpulkan di Loker Portofoliomu ya.',
            'is_read' => false,
        ]);

        // Get inbox as student
        $inboxRes = $this->actingAs($this->studentUser)
            ->getJson(route('circle.messages.inbox'));

        $inboxRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'total_unread' => 1,
            ]);

        // Get conversation
        $convRes = $this->actingAs($this->studentUser)
            ->getJson(route('circle.messages.conversation', $this->teacherUser->id));

        $convRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'contact' => [
                    'id' => $this->teacherUser->id,
                ]
            ]);

        // Message should now be marked as read
        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->teacherUser->id,
            'receiver_id' => $this->studentUser->id,
            'is_read' => true,
        ]);
    }

    public function test_can_search_users_for_message()
    {
        $response = $this->actingAs($this->studentUser)
            ->getJson(route('circle.messages.searchUsers', ['q' => 'Guru']));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $data = $response->json('users');
        $this->assertNotEmpty($data);
        $this->assertEquals($this->teacherUser->id, $data[0]['id']);
    }
}
