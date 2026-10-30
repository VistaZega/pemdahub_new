<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\CircleProfile;
use App\Models\CirclePost;

class CirclePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_circle_feed_page_can_be_rendered()
    {
        $response = $this->get('/circle');
        $response->assertStatus(200);
        $response->assertSee('Pembda');
        $response->assertSee('CIRCLE');
    }

    public function test_circle_profile_page_resolves_for_student()
    {
        $school = School::create([
            'name' => 'SMK Swasta Pembda',
            'type' => 'smk',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Fathan Al-Ghazali',
            'username' => 'fathan',
            'role' => 'siswa',
            'school_id' => $school->id,
            'is_active' => true,
        ]);

        $response = $this->get('/@' . $user->username);
        $response->assertStatus(200);
        $response->assertSee('Fathan Al-Ghazali');

        // Pastikan CircleProfile terbuat
        $profile = CircleProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);

        // Uji akses via username profil circle
        $responseProfile = $this->get('/@' . $profile->username);
        $responseProfile->assertStatus(200);
    }

    public function test_circle_profile_page_resolves_for_teacher()
    {
        $school = School::create([
            'name' => 'SMAS Pembda 1',
            'type' => 'sma',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Budi Santoso, S.Pd',
            'username' => 'budisantoso',
            'role' => 'guru',
            'school_id' => $school->id,
            'is_active' => true,
        ]);

        $response = $this->get('/@' . $user->username);
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso, S.Pd');
    }
}
