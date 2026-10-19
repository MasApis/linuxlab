<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 6: terminal_sessions).
     */
    public function up(): void
    {
        Schema::create('terminal_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // exercise_id bersifat nullable (sesi praktik bebas / latihan terikat).
            // Constraint FK ke exercises akan ditambahkan pada Fase 4 saat tabel exercises dibuat.
            $table->unsignedBigInteger('exercise_id')->nullable()->index();
            $table->string('container_id');
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->string('ws_token', 64)->nullable()->unique();
            $table->timestamp('expires_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_sessions');
    }
};
