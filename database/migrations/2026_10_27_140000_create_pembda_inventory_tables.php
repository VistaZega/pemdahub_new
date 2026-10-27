<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kategori Aset
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->unsignedInteger('useful_life_years')->default(5);
            $table->string('depreciation_method', 50)->default('straight_line');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Lokasi / Ruangan Penempatan Aset
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name', 100);
            $table->string('building_name', 100)->nullable();
            $table->string('room_number', 50)->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('school_id');
        });

        // 3. Master Aset (PembdaINVENTORY)
        Schema::create('inventory_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_number')->default(1); // No Urut
            $table->string('asset_code', 50)->unique(); // Kode Aset
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete(); // Yayasan atau Unit Sekolah
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            
            // Info Dasar
            $table->string('name', 255); // Nama Aset
            $table->string('brand', 100)->nullable(); // Merk / Brand / Tipe
            $table->string('serial_number', 100)->nullable(); // Nomor Seri / No Rangka / Plat
            $table->text('description')->nullable(); // Deskripsi / Spesifikasi

            // Pengadaan
            $table->unsignedSmallInteger('acquisition_year'); // Tahun Pengadaan
            $table->date('acquisition_date')->nullable(); // Tanggal Pengadaan
            $table->string('funding_source', 100)->default('Yayasan'); // Sumber Dana
            $table->unsignedInteger('quantity')->default(1); // Jumlah
            $table->string('unit', 50)->default('Unit'); // Satuan
            $table->decimal('unit_price', 15, 2)->default(0); // Harga Satuan
            $table->decimal('acquisition_price', 15, 2)->default(0); // Total Harga Pengadaan (Jumlah x Harga Satuan)

            // Penyusutan (Depreciation)
            $table->unsignedInteger('useful_life_years')->default(5); // Masa Manfaat dlm Tahun
            $table->string('depreciation_method', 50)->default('straight_line'); // straight_line, none, declining_balance
            $table->decimal('salvage_value', 15, 2)->default(0); // Nilai Residu
            $table->decimal('annual_depreciation', 15, 2)->default(0); // Beban Penyusutan per Tahun
            $table->decimal('monthly_depreciation', 15, 2)->default(0); // Beban Penyusutan per Bulan
            $table->decimal('accumulated_depreciation', 15, 2)->default(0); // Akumulasi Penyusutan
            $table->decimal('current_book_value', 15, 2)->default(0); // Nilai Buku Saat Ini

            // Kondisi & Status
            $table->string('condition', 50)->default('baik'); // baik, rusak_ringan, rusak_berat, hilang, dihapuskan
            $table->string('status', 50)->default('aktif'); // aktif, dipinjam, perbaikan, disimpan, dihapuskan
            
            // Penanggung Jawab & Media
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pic_name', 150)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index('item_number');
            $table->index('school_id');
            $table->index('category_id');
            $table->index('location_id');
            $table->index('acquisition_year');
            $table->index('condition');
            $table->index('status');
        });

        // 4. Akun yang Ditunjuk oleh Yayasan (Delegated Inventory Managers)
        Schema::create('inventory_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete(); // null = Semua Unit & Yayasan
            $table->string('role_title', 100)->default('Pengelola Aset');
            $table->boolean('can_create')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(true);
            $table->boolean('can_export')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('appointed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('appointed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('school_id');
            $table->index('is_active');
        });

        // 5. Riwayat Mutasi / Servis / Perbaikan Aset
        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('inventory_assets')->cascadeOnDelete();
            $table->date('mutation_date');
            $table->string('type', 50); // mutasi_lokasi, perbaikan, pengecekan_rutin, perubahan_kondisi, penghapusan
            $table->foreignId('from_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->string('from_condition', 50)->nullable();
            $table->string('to_condition', 50)->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('description');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('mutation_date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_mutations');
        Schema::dropIfExists('inventory_managers');
        Schema::dropIfExists('inventory_assets');
        Schema::dropIfExists('inventory_locations');
        Schema::dropIfExists('inventory_categories');
    }
};
