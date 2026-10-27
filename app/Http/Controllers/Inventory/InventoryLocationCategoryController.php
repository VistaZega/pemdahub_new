<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryLocation;
use App\Models\School;
use Illuminate\Http\Request;

class InventoryLocationCategoryController extends Controller
{
    /**
     * Master Data Kategori & Lokasi Penempatan Aset
     */
    public function index()
    {
        $categories = InventoryCategory::withCount('assets')->orderBy('name')->get();
        $locations = InventoryLocation::with('school')->withCount('assets')->orderBy('school_id')->orderBy('name')->get();
        $schools = School::where('is_active', true)->orderBy('type')->get();
        $layout = auth()->user()->layout;

        return view('inventory.categories_locations', compact('categories', 'locations', 'schools', 'layout'));
    }

    /**
     * Simpan Kategori Aset Baru
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:inventory_categories,code',
            'useful_life_years' => 'required|integer|min:0|max:100',
            'depreciation_method' => 'required|string|in:straight_line,none,declining_balance',
            'description' => 'nullable|string',
        ]);

        InventoryCategory::create($validated);

        return redirect()->route('inventory.settings.index')
            ->with('success', "Kategori aset '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Hapus Kategori Aset
     */
    public function destroyCategory(InventoryCategory $category)
    {
        if ($category->assets()->exists()) {
            return redirect()->route('inventory.settings.index')
                ->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh aset tercatat.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('inventory.settings.index')
            ->with('success', "Kategori '{$name}' berhasil dihapus.");
    }

    /**
     * Simpan Lokasi / Ruangan Aset Baru
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'nullable',
            'name' => 'required|string|max:100',
            'building_name' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'pic_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        if ($validated['school_id'] === 'yayasan' || empty($validated['school_id'])) {
            $yayasanSchool = School::where('type', 'yayasan')->first();
            $validated['school_id'] = $yayasanSchool ? $yayasanSchool->id : null;
        }

        InventoryLocation::create($validated);

        return redirect()->route('inventory.settings.index')
            ->with('success', "Lokasi / Ruangan '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Hapus Lokasi / Ruangan Aset
     */
    public function destroyLocation(InventoryLocation $location)
    {
        if ($location->assets()->exists()) {
            return redirect()->route('inventory.settings.index')
                ->with('error', "Ruangan '{$location->name}' tidak dapat dihapus karena masih menampung aset tercatat.");
        }

        $name = $location->name;
        $location->delete();

        return redirect()->route('inventory.settings.index')
            ->with('success', "Lokasi '{$name}' berhasil dihapus.");
    }
}
