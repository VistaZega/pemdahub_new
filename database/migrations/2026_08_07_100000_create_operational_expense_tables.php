<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table Expense Categories (Kategori Rekening Pengeluaran)
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Table Operational Expenses (Transaksi Pengeluaran Operasional)
        if (!Schema::hasTable('operational_expenses')) {
            Schema::create('operational_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
                $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
                $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
                $table->string('title');
                $table->date('expense_date');
                $table->decimal('amount', 15, 2);
                $table->string('recipient_name')->nullable();
                $table->string('payment_method')->default('cash'); // cash, transfer
                $table->string('proof_file')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // Seed Default 5 Expense Categories if empty
        $defaultCategories = [
            [
                'name' => 'Subsidi Keuangan',
                'code' => 'EXP-SUB-01',
                'description' => 'Pos alokasi subsidi bantuan keuangan operasional / pendampingan sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Otorisasi',
                'code' => 'EXP-OTO-02',
                'description' => 'Pos pengeluaran otorisasi dana khusus kegiatan / kebijakan pimpinan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operasional',
                'code' => 'EXP-OPE-03',
                'description' => 'Biaya operasional harian, perkakas, ATK, daya & jasa sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tunjangan Bendahara Sekolah',
                'code' => 'EXP-BEN-04',
                'description' => 'Honorarium / tunjangan pengelolaan keuangan Bendahara Sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operator PembdaHUB',
                'code' => 'EXP-OPR-05',
                'description' => 'Insentif / honorarium pengelolaan & pemeliharaan sistem PembdaHUB',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultCategories as $cat) {
            $exists = DB::table('expense_categories')
                ->whereNull('school_id')
                ->where('name', $cat['name'])
                ->exists();
            if (!$exists) {
                DB::table('expense_categories')->insert($cat);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
        Schema::dropIfExists('expense_categories');
    }
};
