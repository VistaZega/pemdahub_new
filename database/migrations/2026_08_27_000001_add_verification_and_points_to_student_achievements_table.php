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
        Schema::table('student_achievements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_achievements', 'status')) {
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('certificate_file');
            }
            if (!Schema::hasColumn('student_achievements', 'points')) {
                $table->integer('points')->default(0)->after('status');
            }
            if (!Schema::hasColumn('student_achievements', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('points')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('student_achievements', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('student_achievements', 'verification_notes')) {
                $table->text('verification_notes')->nullable()->after('verified_at');
            }
        });

        // Set existing records to verified by default
        DB::table('student_achievements')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'verified']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            if (Schema::hasColumn('student_achievements', 'verified_by')) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn('verified_by');
            }
            $columnsToDrop = [];
            foreach (['status', 'points', 'verified_at', 'verification_notes'] as $col) {
                if (Schema::hasColumn('student_achievements', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
