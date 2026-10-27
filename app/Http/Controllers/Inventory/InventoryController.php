<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Models\InventoryCategory;
use App\Models\InventoryLocation;
use App\Models\InventoryMutation;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    /**
     * Dashboard & Daftar Aset Terpadu
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolFilter = $request->get('school_id', 'all');
        $categoryFilter = $request->get('category_id', 'all');
        $conditionFilter = $request->get('condition', 'all');
        $statusFilter = $request->get('status', 'all');
        $yearFilter = $request->get('year', 'all');
        $searchTerm = $request->get('search');

        // Batasi cakupan jika user adalah akun yang ditunjuk dengan keterbatasan unit tertentu
        $manager = $user->inventoryManager;
        if ($manager && $manager->school_id && !$user->canDesignateInventoryManagers()) {
            $schoolFilter = (string) $manager->school_id;
        }

        // Query dasar
        $baseQuery = InventoryAsset::query()
            ->with(['school', 'category', 'location']);

        // Terapkan filter
        if ($schoolFilter === 'yayasan') {
            $baseQuery->where(function ($q) {
                $q->whereNull('school_id')
                  ->orWhereHas('school', fn($sq) => $sq->where('type', 'yayasan'));
            });
        } elseif ($schoolFilter !== 'all' && !empty($schoolFilter)) {
            $baseQuery->where('school_id', $schoolFilter);
        }

        if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
            $baseQuery->where('category_id', $categoryFilter);
        }

        if ($conditionFilter !== 'all' && !empty($conditionFilter)) {
            $baseQuery->where('condition', $conditionFilter);
        }

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $baseQuery->where('status', $statusFilter);
        }

        if ($yearFilter !== 'all' && !empty($yearFilter)) {
            $baseQuery->where('acquisition_year', $yearFilter);
        }

        if (!empty($searchTerm)) {
            $baseQuery->search($searchTerm);
        }

        // Hitung Ringkasan Statistik Finansial & Fisik Real Database
        $statsQuery = clone $baseQuery;
        $totalItems = $statsQuery->sum('quantity');
        $totalAssetCount = $statsQuery->count();
        $totalAcquisitionValue = (float) $statsQuery->sum('acquisition_price');
        $totalAccumulatedDepreciation = (float) $statsQuery->sum('accumulated_depreciation');
        $totalCurrentBookValue = (float) $statsQuery->sum('current_book_value');
        $totalAnnualDepreciation = (float) $statsQuery->sum('annual_depreciation');

        // Statistik Kondisi
        $conditionStatsQuery = clone $baseQuery;
        $conditionStats = $conditionStatsQuery
            ->select('condition', DB::raw('SUM(quantity) as count'))
            ->groupBy('condition')
            ->pluck('count', 'condition')
            ->toArray();

        // Rekapitulasi per Unit Sekolah & Yayasan
        $schools = School::where('is_active', true)->orderBy('type')->get();
        $unitSummary = [];
        
        // Aset Yayasan
        $yysQuery = InventoryAsset::where(function($q) {
            $q->whereNull('school_id')->orWhereHas('school', fn($sq) => $sq->where('type', 'yayasan'));
        });
        $unitSummary['yayasan'] = [
            'name' => 'Yayasan Perguruan Pembda Nias',
            'type' => 'YAYASAN',
            'total_items' => $yysQuery->sum('quantity'),
            'total_value' => (float) $yysQuery->sum('acquisition_price'),
            'book_value' => (float) $yysQuery->sum('current_book_value'),
        ];

        foreach ($schools as $sch) {
            if (strtolower($sch->type) === 'yayasan') continue;
            $uQ = InventoryAsset::where('school_id', $sch->id);
            $unitSummary[$sch->id] = [
                'name' => $sch->name,
                'type' => strtoupper($sch->type),
                'total_items' => $uQ->sum('quantity'),
                'total_value' => (float) $uQ->sum('acquisition_price'),
                'book_value' => (float) $uQ->sum('current_book_value'),
            ];
        }

        // Data Aset Paginated
        $assets = $baseQuery
            ->orderBy('item_number', 'asc')
            ->orderBy('acquisition_year', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Dropdown data
        $categories = InventoryCategory::orderBy('name')->get();
        $locations = InventoryLocation::with('school')->orderBy('name')->get();
        $years = InventoryAsset::distinct()->orderBy('acquisition_year', 'desc')->pluck('acquisition_year');

        $layout = $user->layout;

        return view('inventory.index', compact(
            'assets',
            'schools',
            'categories',
            'locations',
            'years',
            'totalItems',
            'totalAssetCount',
            'totalAcquisitionValue',
            'totalAccumulatedDepreciation',
            'totalCurrentBookValue',
            'totalAnnualDepreciation',
            'conditionStats',
            'unitSummary',
            'schoolFilter',
            'categoryFilter',
            'conditionFilter',
            'statusFilter',
            'yearFilter',
            'searchTerm',
            'layout'
        ));
    }

    /**
     * Form Tambah Aset Baru
     */
    public function create()
    {
        $user = auth()->user();
        $schools = School::where('is_active', true)->orderBy('type')->get();
        $categories = InventoryCategory::orderBy('name')->get();
        $locations = InventoryLocation::with('school')->orderBy('name')->get();
        
        // Daftar staf/guru untuk PIC
        $teachers = User::whereIn('role', ['guru', 'pegawai', 'admin_sekolah', 'superadmin', 'ketua_yayasan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Auto hitung No Urut berikutnya
        $nextItemNumber = ((int) InventoryAsset::max('item_number')) + 1;
        $suggestedCode = InventoryAsset::generateAssetCode(null, 'AST', (int) date('Y'));

        $layout = $user->layout;

        return view('inventory.create', compact(
            'schools',
            'categories',
            'locations',
            'teachers',
            'nextItemNumber',
            'suggestedCode',
            'layout'
        ));
    }

    /**
     * Endpoint API JSON untuk generate kode aset otomatis secara real-time
     */
    public function generateCode(Request $request)
    {
        $schoolId = $request->get('school_id');
        $schoolId = ($schoolId === 'yayasan' || empty($schoolId)) ? null : (int) $schoolId;
        $year = (int) ($request->get('year') ?: date('Y'));

        $code = InventoryAsset::generateAssetCode($schoolId, 'AST', $year);

        // Cari tahu kode terakhir yang ada untuk referensi informasi
        $unitCode = 'YYS';
        $unitName = 'Yayasan Perguruan Pembda Nias (Pusat)';
        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school) {
                $unitName = $school->name;
                $name = strtoupper($school->name);
                if (str_contains($name, 'SMK')) {
                    $unitCode = 'SMK';
                } elseif (str_contains($name, 'SMA')) {
                    $unitCode = 'SMA';
                } elseif (str_contains($name, 'SMP')) {
                    $unitCode = 'SMP';
                }
            }
        }
        $prefix = "AST-{$unitCode}-{$year}-";
        $lastAsset = InventoryAsset::where('asset_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'status' => 'success',
            'code' => $code,
            'prefix' => $prefix,
            'last_code' => $lastAsset ? $lastAsset->asset_code : null,
            'unit_code' => $unitCode,
            'unit_name' => $unitName,
            'year' => $year,
        ]);
    }

    /**
     * Simpan Aset Baru ke Database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_number' => 'nullable|integer|min:1',
            'asset_code' => 'nullable|string|max:50|unique:inventory_assets,asset_code',
            'school_id' => 'nullable',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'acquisition_year' => 'required|integer|min:1950|max:2050',
            'acquisition_date' => 'nullable|date',
            'funding_source' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:0|max:100',
            'depreciation_method' => 'required|string|in:straight_line,none,declining_balance',
            'condition' => 'required|string|in:baik,rusak_ringan,rusak_berat,hilang,dihapuskan',
            'status' => 'required|string|in:aktif,dipinjam,perbaikan,disimpan,dihapuskan',
            'pic_user_id' => 'nullable|exists:users,id',
            'pic_name' => 'nullable|string|max:150',
            'photo' => 'nullable|image|max:3072', // max 3MB
            'notes' => 'nullable|string',
        ]);

        if ($validated['school_id'] === 'yayasan' || empty($validated['school_id'])) {
            $yayasanSchool = School::where('type', 'yayasan')->first();
            $validated['school_id'] = $yayasanSchool ? $yayasanSchool->id : null;
        }

        // Auto hitung No Urut jika tidak diisi user
        if (empty($validated['item_number'])) {
            $validated['item_number'] = ((int) InventoryAsset::max('item_number')) + 1;
        }

        // Auto generate Kode Aset jika tidak diisi user
        if (empty($validated['asset_code'])) {
            $validated['asset_code'] = InventoryAsset::generateAssetCode($validated['school_id'], 'AST', (int) ($validated['acquisition_year'] ?? date('Y')));
        }

        // Upload foto aset
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('inventory_assets', 'public');
            $validated['photo_path'] = $photoPath;
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $asset = new InventoryAsset($validated);
        $asset->calculateDepreciation();
        $asset->save();

        // Catat mutasi awal pengadaan
        InventoryMutation::create([
            'asset_id' => $asset->id,
            'mutation_date' => $asset->acquisition_date ?: now(),
            'type' => 'pengadaan_awal',
            'to_location_id' => $asset->location_id,
            'to_condition' => $asset->condition,
            'cost' => $asset->acquisition_price,
            'description' => 'Pencatatan pengadaan awal aset: ' . $asset->name . ' (' . $asset->quantity . ' ' . $asset->unit . ')',
            'performed_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.show', $asset->id)
            ->with('success', 'Aset baru berhasil ditambahkan ke PembdaINVENTORY dengan Kode: ' . $asset->asset_code);
    }

    /**
     * Detail Aset (Asset 360° Profile & Depreciation Schedule)
     */
    public function show(InventoryAsset $asset)
    {
        $asset->load(['school', 'category', 'location', 'picUser', 'creator', 'updater', 'mutations.fromLocation', 'mutations.toLocation', 'mutations.performer']);
        $depreciationSchedule = $asset->depreciation_schedule;
        $layout = auth()->user()->layout;

        return view('inventory.show', compact('asset', 'depreciationSchedule', 'layout'));
    }

    /**
     * Form Edit Aset
     */
    public function edit(InventoryAsset $asset)
    {
        $user = auth()->user();
        $schools = School::where('is_active', true)->orderBy('type')->get();
        $categories = InventoryCategory::orderBy('name')->get();
        $locations = InventoryLocation::with('school')->orderBy('name')->get();
        
        $teachers = User::whereIn('role', ['guru', 'pegawai', 'admin_sekolah', 'superadmin', 'ketua_yayasan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100)
            ->get();

        $layout = $user->layout;

        return view('inventory.edit', compact(
            'asset',
            'schools',
            'categories',
            'locations',
            'teachers',
            'layout'
        ));
    }

    /**
     * Perbarui Data Aset
     */
    public function update(Request $request, InventoryAsset $asset)
    {
        $validated = $request->validate([
            'item_number' => 'nullable|integer|min:1',
            'asset_code' => 'required|string|max:50|unique:inventory_assets,asset_code,' . $asset->id,
            'school_id' => 'nullable',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'location_id' => 'nullable|exists:inventory_locations,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'acquisition_year' => 'required|integer|min:1950|max:2050',
            'acquisition_date' => 'nullable|date',
            'funding_source' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:0|max:100',
            'depreciation_method' => 'required|string|in:straight_line,none,declining_balance',
            'condition' => 'required|string|in:baik,rusak_ringan,rusak_berat,hilang,dihapuskan',
            'status' => 'required|string|in:aktif,dipinjam,perbaikan,disimpan,dihapuskan',
            'pic_user_id' => 'nullable|exists:users,id',
            'pic_name' => 'nullable|string|max:150',
            'photo' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        if ($validated['school_id'] === 'yayasan' || empty($validated['school_id'])) {
            $yayasanSchool = School::where('type', 'yayasan')->first();
            $validated['school_id'] = $yayasanSchool ? $yayasanSchool->id : null;
        }

        if (empty($validated['item_number'])) {
            $validated['item_number'] = $asset->item_number ?: (((int) InventoryAsset::max('item_number')) + 1);
        }

        // Cek mutasi lokasi atau kondisi
        $oldLocationId = $asset->location_id;
        $oldCondition = $asset->condition;

        if ($request->hasFile('photo')) {
            if ($asset->photo_path) {
                Storage::disk('public')->delete($asset->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('inventory_assets', 'public');
        }

        $validated['updated_by'] = auth()->id();

        $asset->fill($validated);
        $asset->calculateDepreciation();
        $asset->save();

        // Jika lokasi atau kondisi berubah, catat mutasi otomatis
        if ($oldLocationId != $asset->location_id || $oldCondition != $asset->condition) {
            InventoryMutation::create([
                'asset_id' => $asset->id,
                'mutation_date' => now(),
                'type' => $oldLocationId != $asset->location_id ? 'mutasi_lokasi' : 'perubahan_kondisi',
                'from_location_id' => $oldLocationId,
                'to_location_id' => $asset->location_id,
                'from_condition' => $oldCondition,
                'to_condition' => $asset->condition,
                'cost' => 0,
                'description' => 'Pembaruan data aset: perubahan lokasi / kondisi fisik.',
                'performed_by' => auth()->id(),
            ]);
        }

        return redirect()->route('inventory.show', $asset->id)
            ->with('success', 'Data aset ' . $asset->asset_code . ' berhasil diperbarui.');
    }

    /**
     * Hapus Aset dari Database
     */
    public function destroy(InventoryAsset $asset)
    {
        $code = $asset->asset_code;
        $name = $asset->name;

        if ($asset->photo_path) {
            Storage::disk('public')->delete($asset->photo_path);
        }

        $asset->delete();

        return redirect()->route('inventory.index')
            ->with('success', "Aset [{$code}] {$name} berhasil dihapus dari sistem.");
    }

    /**
     * Cetak Label Stiker QR Code & Barcode Fisik Aset
     */
    public function qrLabel(InventoryAsset $asset)
    {
        $asset->load(['school', 'category', 'location']);
        return view('inventory.qr_label', compact('asset'));
    }

    /**
     * Export Dokumen Resmi Laporan Rekapitulasi Inventaris Aset & Penyusutan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $schoolFilter = $request->get('school_id', 'all');
        $categoryFilter = $request->get('category_id', 'all');
        $conditionFilter = $request->get('condition', 'all');
        $yearFilter = $request->get('year', 'all');

        $query = InventoryAsset::with(['school', 'category', 'location']);

        if ($schoolFilter === 'yayasan') {
            $query->where(function ($q) {
                $q->whereNull('school_id')
                  ->orWhereHas('school', fn($sq) => $sq->where('type', 'yayasan'));
            });
            $filterTitle = 'Unit: Yayasan Perguruan Pembda Nias';
        } elseif ($schoolFilter !== 'all' && !empty($schoolFilter)) {
            $query->where('school_id', $schoolFilter);
            $schoolObj = School::find($schoolFilter);
            $filterTitle = 'Unit: ' . ($schoolObj ? $schoolObj->name : 'Unit Sekolah');
        } else {
            $filterTitle = 'Seluruh Unit Sekolah & Yayasan Perguruan Pembda Nias';
        }

        if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
            $query->where('category_id', $categoryFilter);
        }
        if ($conditionFilter !== 'all' && !empty($conditionFilter)) {
            $query->where('condition', $conditionFilter);
        }
        if ($yearFilter !== 'all' && !empty($yearFilter)) {
            $query->where('acquisition_year', $yearFilter);
        }

        $assets = $query->orderBy('item_number')->orderBy('acquisition_year', 'desc')->get();

        $totalItems = $assets->sum('quantity');
        $totalCost = $assets->sum('acquisition_price');
        $totalDep = $assets->sum('accumulated_depreciation');
        $totalBook = $assets->sum('current_book_value');
        $totalAnnualDep = $assets->sum('annual_depreciation');

        $pdf = Pdf::loadView('inventory.pdf', compact(
            'assets',
            'filterTitle',
            'totalItems',
            'totalCost',
            'totalDep',
            'totalBook',
            'totalAnnualDep'
        ))->setPaper('a4', 'landscape');

        $filename = 'PembdaINVENTORY-Rekap-Aset-' . date('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Rekapitulasi Data Aset ke Format CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $assets = InventoryAsset::with(['school', 'category', 'location'])
            ->orderBy('item_number')
            ->get();

        $filename = 'PembdaINVENTORY-Data-Aset-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($assets) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar Excel membaca karakter khusus dengan benar
            fputs($file, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($file, [
                'No Urut',
                'Kode Aset',
                'Unit / Sekolah',
                'Kategori',
                'Nama Aset',
                'Merk / Tipe',
                'Nomor Seri',
                'Lokasi / Ruangan',
                'Tahun Pengadaan',
                'Sumber Dana',
                'Jumlah',
                'Satuan',
                'Harga Satuan (Rp)',
                'Total Harga Pengadaan (Rp)',
                'Masa Manfaat (Tahun)',
                'Nilai Residu (Rp)',
                'Beban Penyusutan / Tahun (Rp)',
                'Akumulasi Penyusutan (Rp)',
                'Nilai Buku Saat Ini (Rp)',
                'Kondisi',
                'Status',
                'Penanggung Jawab (PIC)',
            ]);

            $no = 1;
            foreach ($assets as $asset) {
                fputcsv($file, [
                    $no++,
                    $asset->asset_code,
                    $asset->unit_name,
                    $asset->category ? $asset->category->name : '-',
                    $asset->name,
                    $asset->brand ?: '-',
                    $asset->serial_number ?: '-',
                    $asset->location ? $asset->location->name : '-',
                    $asset->acquisition_year,
                    $asset->funding_source,
                    $asset->quantity,
                    $asset->unit,
                    $asset->unit_price,
                    $asset->acquisition_price,
                    $asset->useful_life_years,
                    $asset->salvage_value,
                    $asset->annual_depreciation,
                    $asset->accumulated_depreciation,
                    $asset->current_book_value,
                    ucfirst(str_replace('_', ' ', $asset->condition)),
                    ucfirst(str_replace('_', ' ', $asset->status)),
                    $asset->pic_name ?: '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Hitung Ulang Akumulasi Penyusutan Seluruh Aset untuk Tahun Berjalan
     */
    public function recalculateAll()
    {
        $assets = InventoryAsset::all();
        $count = 0;
        foreach ($assets as $asset) {
            $asset->calculateDepreciation();
            $asset->saveQuietly();
            $count++;
        }

        return redirect()->route('inventory.index')
            ->with('success', "Berhasil menyinkronkan & memperbarui kalkulasi penyusutan untuk {$count} aset.");
    }
}
