<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('board_type', ['uno', 'nano', 'esp32'])->default('uno');
            $table->longText('circuit_json')->nullable();
            $table->longText('code_ino')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('user_type')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_public')->default(true);
            $table->string('share_token')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_projects');
    }
};
