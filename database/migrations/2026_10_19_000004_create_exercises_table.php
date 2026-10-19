<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 4: exercises).
     */
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instruction');
            $table->enum('validation_type', ['system_state', 'output', 'combined']);
            $table->json('validation_config');
            $table->unsignedInteger('points')->default(100);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->index(['lesson_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
