<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 5: hints).
     */
    public function up(): void
    {
        Schema::create('hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->index(['exercise_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hints');
    }
};
