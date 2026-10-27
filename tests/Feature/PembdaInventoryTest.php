<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\InventoryAsset;
use App\Models\InventoryCategory;
use App\Models\InventoryLocation;
use App\Models\InventoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PembdaInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_unauthorized_user_cannot_access_inventory()
    {
        // Siswa or normal non-appointed user
        $studentUser = User::where('role', 'siswa')->first() ?: User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($studentUser)->get(route('inventory.index'));
        $response->assertStatus(403);
    }

    public function test_yayasan_user_can_access_inventory()
    {
        $yayasanUser = User::where('role', 'ketua_yayasan')->orWhere('username', 'yulzega')->first();
        if (!$yayasanUser) {
            $yayasanUser = User::factory()->create(['role' => 'ketua_yayasan', 'username' => 'ketua_yys']);
        }

        $response = $this->actingAs($yayasanUser)->get(route('inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('PembdaINVENTORY');
        $response->assertSee('Total Harga Pengadaan');
    }

    public function test_appointed_user_can_access_inventory()
    {
        $teacher = User::where('role', 'guru')->first() ?: User::factory()->create(['role' => 'guru']);

        // Sebelum ditunjuk -> 403
        $this->actingAs($teacher)->get(route('inventory.index'))->assertStatus(403);

        // Yayasan menunjuk guru ini
        InventoryManager::updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'role_title' => 'Koordinator Sarpras Sekolah',
                'is_active' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_export' => true,
            ]
        );

        // Setelah ditunjuk -> 200 OK
        $this->actingAs($teacher->fresh())->get(route('inventory.index'))->assertStatus(200);
    }

    public function test_asset_depreciation_calculation()
    {
        $category = InventoryCategory::where('code', 'KOM')->first();
        $location = InventoryLocation::first();

        $asset = new InventoryAsset([
            'item_number' => 999,
            'asset_code' => 'AST-TEST-2026-9999',
            'name' => 'Laptop Testing Unit',
            'category_id' => $category?->id,
            'location_id' => $location?->id,
            'acquisition_year' => 2024,
            'acquisition_date' => '2024-01-01',
            'funding_source' => 'Yayasan',
            'quantity' => 2,
            'unit' => 'Unit',
            'unit_price' => 10000000, // Total 20.000.000
            'salvage_value' => 2000000, // Residu 2.000.000
            'useful_life_years' => 4,   // 4 Tahun
            'depreciation_method' => 'straight_line',
            'condition' => 'baik',
            'status' => 'aktif',
        ]);

        $asset->calculateDepreciation();

        // Depreciable base = 20.000.000 - 2.000.000 = 18.000.000
        // Annual depreciation = 18.000.000 / 4 = 4.500.000
        $this->assertEquals(20000000, $asset->acquisition_price);
        $this->assertEquals(4500000, $asset->annual_depreciation);
        $this->assertEquals(375000, $asset->monthly_depreciation);
        $this->assertGreaterThan(0, $asset->accumulated_depreciation);
        $this->assertLessThanOrEqual(20000000, $asset->current_book_value);
    }
}
