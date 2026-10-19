<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Module;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Jalankan seeder master lencana awal sesuai SPEC Bagian 12.
     */
    public function run(): void
    {
        $fsModule = Module::where('slug', 'file-system')->first();
        $permModule = Module::where('slug', 'file-permissions')->first();
        $textModule = Module::where('slug', 'text-processing')->first();

        $badges = [
            [
                'name' => 'Langkah Pertama',
                'description' => 'Menyelesaikan latihan pertama dengan benar.',
                'icon' => 'rocket_launch',
                'criteria_type' => 'first_exercise',
                'criteria_value' => null,
            ],
            [
                'name' => 'Penjelajah File System',
                'description' => 'Menyelesaikan seluruh materi dan latihan pada Modul Dasar File System.',
                'icon' => 'folder_open',
                'criteria_type' => 'module_completed',
                'criteria_value' => $fsModule ? ['module_id' => $fsModule->id] : null,
            ],
            [
                'name' => 'Penjaga Izin',
                'description' => 'Menyelesaikan seluruh materi dan latihan pada Modul Permission.',
                'icon' => 'security',
                'criteria_type' => 'module_completed',
                'criteria_value' => $permModule ? ['module_id' => $permModule->id] : null,
            ],
            [
                'name' => 'Ahli Teks',
                'description' => 'Menyelesaikan seluruh materi dan latihan pada Modul Text Processing.',
                'icon' => 'terminal',
                'criteria_type' => 'module_completed',
                'criteria_value' => $textModule ? ['module_id' => $textModule->id] : null,
            ],
            [
                'name' => 'Pembelajar Tekun',
                'description' => 'Telah menyelesaikan benar sekurang-kurangnya 10 latihan berbeda.',
                'icon' => 'school',
                'criteria_type' => 'exercises_count',
                'criteria_value' => ['count' => 10],
            ],
            [
                'name' => 'Suka Bertanya',
                'description' => 'Membuat pertanyaan pertama di forum diskusi.',
                'icon' => 'help',
                'criteria_type' => 'first_question',
                'criteria_value' => null,
            ],
            [
                'name' => 'Penolong Komunitas',
                'description' => 'Jawabannya ditandai sebagai jawaban terbaik sekurang-kurangnya 1 kali.',
                'icon' => 'verified',
                'criteria_type' => 'best_answer',
                'criteria_value' => ['count' => 1],
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['name' => $badgeData['name']],
                $badgeData
            );
        }
    }
}
