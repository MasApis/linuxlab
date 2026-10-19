<?php

namespace App\Console\Commands;

use App\Services\TerminalService;
use Illuminate\Console\Command;

class CleanExpiredTerminalSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminal:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan sesi terminal yang telah melewati batas waktu (FR-TERM-04)';

    /**
     * Execute the console command.
     */
    public function handle(TerminalService $terminalService): int
    {
        $this->info('Memeriksa dan membersihkan sesi terminal kedaluwarsa...');

        $cleanedCount = $terminalService->cleanExpiredSessions();

        $this->info("Berhasil membersihkan {$cleanedCount} sesi terminal kedaluwarsa.");

        return Command::SUCCESS;
    }
}
