<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghubungkan foreign key exercise_id pada terminal_sessions ke tabel exercises (SPEC Bagian 8.3).
     */
    public function up(): void
    {
        Schema::table('terminal_sessions', function (Blueprint $table) {
            $table->foreign('exercise_id')->references('id')->on('exercises')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminal_sessions', function (Blueprint $table) {
            $table->dropForeign(['exercise_id']);
        });
    }
};
