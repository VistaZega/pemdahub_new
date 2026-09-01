<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_courses', 'review_status')) {
                $table->string('review_status', 32)->default('approved')->after('is_active')
                    ->comment('unreviewed|pending|approved|rejected');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('review_status');
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $table->text('review_note')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'reviewed_by', 'reviewed_at', 'review_note']);
        });
    }
};