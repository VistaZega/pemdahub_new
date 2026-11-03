<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add handwriting_image to cbt_answers table if not exists
        if (Schema::hasTable('cbt_answers') && !Schema::hasColumn('cbt_answers', 'handwriting_image')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->string('handwriting_image')->nullable()->after('text_answer')->comment('File path gambar tulisan tangan siswa');
            });
        }

        // 2. Make option_text nullable in cbt_question_options for pure image options
        if (Schema::hasTable('cbt_question_options') && Schema::hasColumn('cbt_question_options', 'option_text')) {
            Schema::table('cbt_question_options', function (Blueprint $table) {
                $table->text('option_text')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cbt_answers') && Schema::hasColumn('cbt_answers', 'handwriting_image')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->dropColumn('handwriting_image');
            });
        }

        if (Schema::hasTable('cbt_question_options') && Schema::hasColumn('cbt_question_options', 'option_text')) {
            Schema::table('cbt_question_options', function (Blueprint $table) {
                $table->text('option_text')->nullable(false)->change();
            });
        }
    }
};
