<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\InventoryCategory;
use App\Models\InventoryLocation;
use App\Models\InventoryAsset;
use App\Models\User;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kategori Aset
        $categories = [
            [
                'name' => 'Tanah & Lahan',
                'code' => 'TNH',
                'useful_life_years' => 0,
                'depreciation_method' => 'none',
                'description' => 'Tanah persil kampus, lapangan upacara, dan lahan sarana praktik',
            ],
            [
                'name' => 'Gedung & Bangunan',
                'code' => 'GDG',
                'useful_life_years' => 20,
                'depreciation_method' => 'straight_line',
                'description' => 'Gedung kantor yayasan, ruang kelas teori, lab, dan bengkel praktik',
            ],
            [
                'name' => 'Peralatan Komputer & IT',
                'code' => 'KOM',
                'useful_life_years' => 4,
                'depreciation_method' => 'straight_line',
                'description' => 'Komputer PC, laptop, server jaringan, proyektor, printer, dan router',
            ],
            [
                'name' => 'Mesin Bengkel & Lab Kejuruan',
                'code' => 'MSN',
                'useful_life_years' => 8,
                'depreciation_method' => 'straight_line',
                'description' => 'Mesin perkakas, simulator otomotif, alat ukur kejuruan, trainer kelistrikan',
            ],
            [
                'name' => 'Alat Praktik & Sains',
                'code' => 'LAB',
                'useful_life_years' => 5,
                'depreciation_method' => 'straight_line',
                'description' => 'Peralatan laboratorium IPA, mikroskop, tabung reaksi, kit fisika/biologi',
            ],
            [
                'name' => 'Alat Musik & Marching Band',
                'code' => 'MSK',
                'useful_life_years' => 5,
                'depreciation_method' => 'straight_line',
                'description' => 'Instrumen marching band Pembda, drum, brass, keyboard, sound system',
            ],
            [
                'name' => 'Kendaraan Dinas & Operasional',
                'code' => 'KND',
                'useful_life_years' => 8,
                'depreciation_method' => 'straight_line',
                'description' => 'Mobil dinas yayasan, bus sekolah, dan sepeda motor operasional unit',
            ],
            [
                'name' => 'Mebeul & Perabot Ruangan',
                'code' => 'MBL',
                'useful_life_years' => 5,
                'depreciation_method' => 'straight_line',
                'description' => 'Meja kursi siswa/guru, lemari arsip, papan tulis, rak buku perpustakaan',
            ],
            [
                'name' => 'Koleksi Buku & Modul',
                'code' => 'BKU',
                'useful_life_years' => 5,
                'depreciation_method' => 'straight_line',
                'description' => 'Buku teks kurikulum merdeka, referensi perpustakaan, kamus, ensiklopedia',
            ],
            [
                'name' => 'Perlengkapan & Sarana Lainnya',
                'code' => 'LAIN',
                'useful_life_years' => 5,
                'depreciation_method' => 'straight_line',
                'description' => 'Genset listrik, AC pendingin ruangan, dispenser, tangki tandon air',
            ],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['code']] = InventoryCategory::firstOrCreate(
                ['code' => $cat['code']],
                $cat
            );
        }

        // Cari Unit Sekolah & Yayasan
        $yayasanSchool = School::where('type', 'yayasan')->first();
        $smaSchool = School::where('name', 'like', '%SMAS Pembda 1%')->first();
        $smkSchool = School::where('name', 'like', '%SMK Swasta Pembda%')->first();
        $smpSchool = School::where('name', 'like', '%SMPS Pembda 2%')->first();

        // 2. Lokasi Penempatan Aset
        $locations = [
            // Yayasan
            [
                'school_id' => $yayasanSchool?->id,
                'name' => 'Kantor Pengurus Yayasan',
                'building_name' => 'Gedung Pusat Yayasan Lt. 1',
                'room_number' => 'YYS-01',
                'pic_name' => 'Sekretariat Yayasan',
            ],
            [
                'school_id' => $yayasanSchool?->id,
                'name' => 'Ruang Rapat Utama Yayasan',
                'building_name' => 'Gedung Pusat Yayasan Lt. 1',
                'room_number' => 'YYS-02',
                'pic_name' => 'Sekretariat Yayasan',
            ],
            // SMA
            [
                'school_id' => $smaSchool?->id,
                'name' => 'Laboratorium Komputer SMA',
                'building_name' => 'Gedung Laboratorium SMA Lt. 2',
                'room_number' => 'LAB-SMA-01',
                'pic_name' => 'Kepala Lab Komputer SMA',
            ],
            [
                'school_id' => $smaSchool?->id,
                'name' => 'Ruang Guru & Tata Usaha SMA',
                'building_name' => 'Gedung Utama SMA Lt. 1',
                'room_number' => 'TU-SMA',
                'pic_name' => 'Staf Tata Usaha SMA',
            ],
            // SMK
            [
                'school_id' => $smkSchool?->id,
                'name' => 'Bengkel Praktik Otomotif (TKR & TBSM)',
                'building_name' => 'Workshop Kejuruan SMK',
                'room_number' => 'BGK-OTO',
                'pic_name' => 'Kepala Bengkel Otomotif',
            ],
            [
                'school_id' => $smkSchool?->id,
                'name' => 'Laboratorium Jaringan Komputer & Axioo (TKJ & ACP)',
                'building_name' => 'Gedung Lab IT SMK Lt. 2',
                'room_number' => 'LAB-TKJ',
                'pic_name' => 'Kepala Lab TKJ',
            ],
            [
                'school_id' => $smkSchool?->id,
                'name' => 'Studio Gambar & Pemodelan Bangunan (DPIB)',
                'building_name' => 'Gedung Kejuruan DPIB',
                'room_number' => 'STD-DPIB',
                'pic_name' => 'Koordinator Konsentrasi DPIB',
            ],
            // SMP
            [
                'school_id' => $smpSchool?->id,
                'name' => 'Laboratorium IPA Terpadu SMP',
                'building_name' => 'Gedung SMP Lt. 1',
                'room_number' => 'LAB-IPA-SMP',
                'pic_name' => 'Guru Pengelola Lab IPA',
            ],
            [
                'school_id' => $smpSchool?->id,
                'name' => 'Ruang Kelas & Belajar SMP',
                'building_name' => 'Gedung Belajar SMP',
                'room_number' => 'SMP-01',
                'pic_name' => 'Wali Kelas SMP',
            ],
        ];

        $locModels = [];
        foreach ($locations as $loc) {
            $locModels[$loc['name']] = InventoryLocation::firstOrCreate(
                ['name' => $loc['name'], 'school_id' => $loc['school_id']],
                $loc
            );
        }

        // Cari User Admin / Creator
        $adminUser = User::where('role', 'superadmin')
            ->orWhere('role', 'ketua_yayasan')
            ->orWhere('username', 'yulzega')
            ->first();

        // 3. Sample Aset Awal PembdaINVENTORY
        $assets = [
            // Aset Yayasan
            [
                'item_number' => 1,
                'asset_code' => 'AST-YYS-2023-0001',
                'school_id' => $yayasanSchool?->id,
                'category_id' => $catModels['GDG']->id,
                'location_id' => $locModels['Kantor Pengurus Yayasan']->id,
                'name' => 'Gedung Kantor Yayasan & Ruang Pertemuan',
                'brand' => 'Konstruksi Beton Bertingkat',
                'serial_number' => 'IMB-YYS-2023',
                'description' => 'Bangunan kantor pusat Yayasan Perguruan Pembda Nias 2 lantai seluas 450 m2',
                'acquisition_year' => 2023,
                'acquisition_date' => '2023-01-15',
                'funding_source' => 'Yayasan Mandiri',
                'quantity' => 1,
                'unit' => 'Unit Gedung',
                'unit_price' => 750000000,
                'useful_life_years' => 20,
                'salvage_value' => 50000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Ketua Yayasan Perguruan Pembda',
            ],
            [
                'item_number' => 2,
                'asset_code' => 'AST-YYS-2024-0002',
                'school_id' => $yayasanSchool?->id,
                'category_id' => $catModels['KND']->id,
                'location_id' => $locModels['Kantor Pengurus Yayasan']->id,
                'name' => 'Mobil Dinas Operasional Yayasan (Toyota Avanza 1.5 G)',
                'brand' => 'Toyota Avanza',
                'serial_number' => 'BB 1928 BB / MHFA2BA3...',
                'description' => 'Kendaraan dinas operasional yayasan dan monitoring seluruh unit sekolah',
                'acquisition_year' => 2024,
                'acquisition_date' => '2024-03-10',
                'funding_source' => 'Yayasan',
                'quantity' => 1,
                'unit' => 'Unit Kendaraan',
                'unit_price' => 245000000,
                'useful_life_years' => 8,
                'salvage_value' => 45000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Bagian Perlengkapan Yayasan',
            ],
            [
                'item_number' => 3,
                'asset_code' => 'AST-YYS-2025-0003',
                'school_id' => $yayasanSchool?->id,
                'category_id' => $catModels['MSK']->id,
                'location_id' => $locModels['Kantor Pengurus Yayasan']->id,
                'name' => 'Satu Set Lengkap Instrumen Marching Band Pembda Nias',
                'brand' => 'Yamaha Marching Brass & Percussion',
                'serial_number' => 'MB-PEMBDA-2025',
                'description' => 'Snare drum, bass drum, tenor quad, marching trumpet, tuba, dan perlengkapan seragam',
                'acquisition_year' => 2025,
                'acquisition_date' => '2025-05-20',
                'funding_source' => 'Bantuan Alumni & Yayasan',
                'quantity' => 1,
                'unit' => 'Set Lengkap',
                'unit_price' => 85000000,
                'useful_life_years' => 5,
                'salvage_value' => 5000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Pembina Marching Band Pembda',
            ],

            // Aset SMAS Pembda 1
            [
                'item_number' => 1,
                'asset_code' => 'AST-SMA-2024-0001',
                'school_id' => $smaSchool?->id,
                'category_id' => $catModels['KOM']->id,
                'location_id' => $locModels['Laboratorium Komputer SMA']->id,
                'name' => 'PC All-in-One Laboratorium Komputer SMA (Core i5 12th Gen)',
                'brand' => 'HP 24 All-in-One PC',
                'serial_number' => 'HP-AIO-2024-01-30',
                'description' => 'Komputer client CBT, pembelajaran digital, dan riset ilmiah siswa SMA Pembda 1',
                'acquisition_year' => 2024,
                'acquisition_date' => '2024-07-05',
                'funding_source' => 'BOS Kinerja & Yayasan',
                'quantity' => 25,
                'unit' => 'Unit PC',
                'unit_price' => 9200000,
                'useful_life_years' => 4,
                'salvage_value' => 10000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Kepala Lab Komputer SMA',
            ],
            [
                'item_number' => 2,
                'asset_code' => 'AST-SMA-2025-0002',
                'school_id' => $smaSchool?->id,
                'category_id' => $catModels['KOM']->id,
                'location_id' => $locModels['Laboratorium Komputer SMA']->id,
                'name' => 'Proyektor Laser Interaktif Kelas (Epson EB-735F Full HD)',
                'brand' => 'Epson EB-735F',
                'serial_number' => 'EPS-EB-9872',
                'description' => 'Proyektor ultra short-throw interaktif untuk smart classroom SMA',
                'acquisition_year' => 2025,
                'acquisition_date' => '2025-02-12',
                'funding_source' => 'BOS Reguler',
                'quantity' => 4,
                'unit' => 'Unit',
                'unit_price' => 18500000,
                'useful_life_years' => 4,
                'salvage_value' => 4000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Koordinator Sarpras SMA',
            ],

            // Aset SMK Swasta Pembda Nias
            [
                'item_number' => 1,
                'asset_code' => 'AST-SMK-2023-0001',
                'school_id' => $smkSchool?->id,
                'category_id' => $catModels['MSN']->id,
                'location_id' => $locModels['Bengkel Praktik Otomotif (TKR & TBSM)']->id,
                'name' => 'Two Post Car Lift 4 Ton Bengkel TKR',
                'brand' => 'Launch TLT240SC',
                'serial_number' => 'LCH-LIFT-2023-88',
                'description' => 'Hydraulic two post lift kapasitas angkat 4.000 kg untuk servis kendaraan ringan bengkel SMK',
                'acquisition_year' => 2023,
                'acquisition_date' => '2023-08-18',
                'funding_source' => 'DAK Fisik SMK',
                'quantity' => 2,
                'unit' => 'Unit',
                'unit_price' => 38000000,
                'useful_life_years' => 8,
                'salvage_value' => 8000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Kepala Bengkel Otomotif SMK',
            ],
            [
                'item_number' => 2,
                'asset_code' => 'AST-SMK-2024-0002',
                'school_id' => $smkSchool?->id,
                'category_id' => $catModels['MSN']->id,
                'location_id' => $locModels['Bengkel Praktik Otomotif (TKR & TBSM)']->id,
                'name' => 'Trainer Engine EFI Simulator Bensin & Stand Uji',
                'brand' => 'Toyota 1NZ-FE Simulator VVT-i',
                'serial_number' => 'TRN-ENG-2024-01',
                'description' => 'Mesin simulator injeksi bahan bakar elektronik lengkap instrumen OBD-II diagnostic',
                'acquisition_year' => 2024,
                'acquisition_date' => '2024-04-14',
                'funding_source' => 'BOS Reguler SMK',
                'quantity' => 2,
                'unit' => 'Set Simulator',
                'unit_price' => 42000000,
                'useful_life_years' => 8,
                'salvage_value' => 6000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Instruktur Bengkel TKR',
            ],
            [
                'item_number' => 3,
                'asset_code' => 'AST-SMK-2025-0003',
                'school_id' => $smkSchool?->id,
                'category_id' => $catModels['KOM']->id,
                'location_id' => $locModels['Laboratorium Jaringan Komputer & Axioo (TKJ & ACP)']->id,
                'name' => 'PC Workstation Axioo MyPC Pro L7 (Intel Core i7 32GB RAM)',
                'brand' => 'Axioo MyPC Pro',
                'serial_number' => 'AXO-L7-2025-10..40',
                'description' => 'Workstation sertifikasi Axioo Class Program, simulasi jaringan Mikrotik & Cisco',
                'acquisition_year' => 2025,
                'acquisition_date' => '2025-01-20',
                'funding_source' => 'Kerjasama Industri Axioo & Yayasan',
                'quantity' => 30,
                'unit' => 'Unit PC',
                'unit_price' => 12500000,
                'useful_life_years' => 4,
                'salvage_value' => 30000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Kepala Lab TKJ / ACP SMK',
            ],

            // Aset SMPS Pembda 2
            [
                'item_number' => 1,
                'asset_code' => 'AST-SMP-2024-0001',
                'school_id' => $smpSchool?->id,
                'category_id' => $catModels['LAB']->id,
                'location_id' => $locModels['Laboratorium IPA Terpadu SMP']->id,
                'name' => 'Mikroskop Binokuler Pembelajaran Sains SMP',
                'brand' => 'Olympus CX23',
                'serial_number' => 'OLY-CX23-01..10',
                'description' => 'Mikroskop biologi LED optik pembesaran hingga 1000x untuk praktikum siswa SMP',
                'acquisition_year' => 2024,
                'acquisition_date' => '2024-09-02',
                'funding_source' => 'BOS Reguler SMP',
                'quantity' => 10,
                'unit' => 'Unit Mikroskop',
                'unit_price' => 6500000,
                'useful_life_years' => 5,
                'salvage_value' => 5000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Guru Pengelola Lab IPA SMP',
            ],
            [
                'item_number' => 2,
                'asset_code' => 'AST-SMP-2025-0002',
                'school_id' => $smpSchool?->id,
                'category_id' => $catModels['MBL']->id,
                'location_id' => $locModels['Ruang Kelas & Belajar SMP']->id,
                'name' => 'Set Meja Kursi Siswa Ergonomis Kayu Jati Lapis Besi',
                'brand' => 'Informa Education Ergonomic',
                'serial_number' => 'FUR-SMP-2025',
                'description' => 'Meja kursi belajar siswa ruang kelas SMP rangka besi kokoh tahan lama',
                'acquisition_year' => 2025,
                'acquisition_date' => '2025-06-15',
                'funding_source' => 'Yayasan & Komite',
                'quantity' => 60,
                'unit' => 'Set Meja Kursi',
                'unit_price' => 850000,
                'useful_life_years' => 5,
                'salvage_value' => 3000000,
                'condition' => 'baik',
                'status' => 'aktif',
                'pic_name' => 'Staf Tata Usaha SMP',
            ],
        ];

        foreach ($assets as $assetData) {
            $assetData['created_by'] = $adminUser?->id;
            $assetData['updated_by'] = $adminUser?->id;
            
            $asset = InventoryAsset::firstOrNew(['asset_code' => $assetData['asset_code']]);
            $asset->fill($assetData);
            $asset->calculateDepreciation();
            $asset->save();
        }

        // 4. Pastikan Akun Bendahara Terdaftar sebagai Pengelola Aset Resmi
        $dewiUser = User::where('email', 'dewi@pembdahub.com')->orWhere('username', 'dewi')->first();
        if ($dewiUser) {
            \Illuminate\Support\Facades\DB::table('inventory_managers')->where('user_id', $dewiUser->id)->delete();
        }

        $treasurers = [
            ['email' => 'bendaharasmp@pembdahub.com', 'username' => 'bendaharasmp', 'school_id' => $smpSchool?->id ?: 9, 'role_title' => 'Bendahara & Pengelola Aset SMPS Pembda 2'],
            ['email' => 'bendaharasmk@pembdahub.com', 'username' => 'bendaharasmk', 'school_id' => $smkSchool?->id ?: 7, 'role_title' => 'Bendahara & Pengelola Aset SMK Swasta Pembda Nias (Loket 1)'],
            ['email' => 'bendaharasmk2@pembdahub.com', 'username' => 'bendaharasmk2', 'school_id' => $smkSchool?->id ?: 7, 'role_title' => 'Bendahara & Pengelola Aset SMK Swasta Pembda Nias (Loket 2)'],
            ['email' => 'bendaharasma@pembdahub.com', 'username' => 'bendaharasma', 'school_id' => $smaSchool?->id ?: 2, 'role_title' => 'Bendahara & Pengelola Aset SMAS Pembda 1 Gunungsitoli'],
            ['email' => 'yuniria@pembdahub.com', 'username' => 'yuniria', 'school_id' => $smaSchool?->id ?: 2, 'role_title' => 'Staff Keuangan & Pengelola Aset SMAS Pembda 1'],
            ['email' => 'bendaharayayasan@pembdahub.com', 'username' => 'bendaharayayasan', 'school_id' => $yayasanSchool?->id ?: 4, 'role_title' => 'Bendahara Yayasan & Pengawas Aset Terpadu'],
        ];

        foreach ($treasurers as $t) {
            $user = User::where('email', $t['email'])->orWhere('username', $t['username'])->first();
            if ($user) {
                \Illuminate\Support\Facades\DB::table('inventory_managers')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'school_id' => $t['school_id'],
                        'role_title' => $t['role_title'],
                        'can_create' => 1,
                        'can_edit' => 1,
                        'can_delete' => 1,
                        'can_export' => 1,
                        'is_active' => 1,
                        'appointed_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
