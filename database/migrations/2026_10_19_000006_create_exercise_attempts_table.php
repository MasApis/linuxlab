<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 7: exercise_attempts).
     */
    public function up(): void
    {
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('terminal_session_id')->nullable()->constrained('terminal_sessions')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score')->default(0);
            $table->text('feedback');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'exercise_id']);
            $table->index(['user_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
    }
};
