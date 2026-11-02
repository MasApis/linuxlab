<?php

namespace App\Services;

use App\Models\TerminalSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class TerminalService
{
    /**
     * Membuat container terisolasi baru dan mencatat sesi di database (FR-TERM-01, SEC-TERM-01 s.d. 08).
     *
     * @throws RuntimeException Bila server penuh atau pembuatan container gagal
     */
    public function createSession(User $user, ?int $exerciseId = null): TerminalSession
    {
        // Kunci baris user agar pembuatan sesi konkuren terserialisasi (FR-TERM-01:
        // satu pengguna hanya boleh memiliki satu sesi aktif). Cek kuota + insert
        // berjalan dalam transaksi yang sama sehingga tidak bisa disalip.
        return DB::transaction(function () use ($user, $exerciseId) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            // 1. Bersihkan sesi kedaluwarsa terlebih dahulu agar kapasitas kembali akurat
            $this->cleanExpiredSessions();

            // 2. Periksa batas jumlah sesi aktif bersamaan (SEC-TERM-08)
            $maxSessions = (int) config('terminal.max_sessions', 5);
            $activeSessionsCount = TerminalSession::query()->active()->count();

            // Ambil sesi aktif user saat ini jika ada
            $existingSession = TerminalSession::query()
                ->where('user_id', $user->id)
                ->active()
                ->first();

            // Jika user belum punya sesi aktif dan server sudah mencapai kuota maksimal
            if (! $existingSession && $activeSessionsCount >= $maxSessions) {
                throw new RuntimeException('Server praktik sedang penuh, coba lagi sesaat lagi.');
            }

            // 3. Satu pengguna hanya boleh memiliki satu sesi aktif (FR-TERM-01, D-03)
            // Memulai sesi baru otomatis mengakhiri sesi lama
            if ($existingSession) {
                $this->destroySession($existingSession, 'terminated');
            }

            // 4. Siapkan konfigurasi container terisolasi (SEC-TERM-02, SEC-TERM-03, SEC-TERM-04)
            $image = (string) config('terminal.image', 'linuxlab-terminal:latest');
            $cpuLimit = (string) config('terminal.cpu_limit', '0.5');
            $memoryLimit = (string) config('terminal.memory_limit', '256m');
            $pidsLimit = (string) config('terminal.pids_limit', 64);
            $ttlMinutes = (int) config('terminal.session_ttl', 30);

            $uuid = Str::uuid()->toString();
            $containerName = "linuxlab-session-{$user->id}-{$uuid}";
            $wsToken = Str::random(64);

            // Perintah pembuatan container terisolasi:
            // - Non-root: --user student (SEC-TERM-03)
            // - Batas CPU: --cpus (SEC-TERM-03)
            // - Batas Memori: -m (SEC-TERM-03)
            // - Batas Proses: --pids-limit (SEC-TERM-03)
            // - Tanpa Akses Jaringan Luar: --network none (SEC-TERM-04)
            // - Kurangi kapabilitas kernel + cegah privilege escalation (hardening)
            // - Direktori Kerja: -w /home/student
            $cmd = [
                'docker', 'run', '-d',
                '--name', $containerName,
                '--cpus', $cpuLimit,
                '-m', $memoryLimit,
                '--pids-limit', $pidsLimit,
                '--network', 'none',
                '--cap-drop', 'ALL',
                '--security-opt', 'no-new-privileges:true',
                '--user', 'student',
                '-w', '/home/student',
                $image,
                'sleep', 'infinity',
            ];

            $process = new Process($cmd);
            $process->setTimeout(30);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('Gagal membuat container terminal: '.$process->getErrorOutput());
                throw new RuntimeException('Gagal menyiapkan lingkungan terminal praktik. Silakan hubungi pengelola.');
            }

            $containerId = trim($process->getOutput());
            if (empty($containerId)) {
                $containerId = $containerName;
            }

            // 5. Catat sesi pada tabel terminal_sessions (FR-TERM-01, SPEC Bagian 8.2)
            return TerminalSession::create([
                'user_id' => $user->id,
                'exercise_id' => $exerciseId,
                'container_id' => $containerId,
                'status' => 'active',
                'ws_token' => $wsToken,
                'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
                ]);
        });
    }

    /**
     * Menghancurkan container sesi dan menandai status di database (FR-TERM-04, SEC-TERM-06).
     */
    public function destroySession(TerminalSession $session, string $status = 'terminated'): bool
    {
        $containerId = $session->container_id;

        // Hancurkan container di Docker (SEC-TERM-06)
        if ($containerId) {
            $this->destroyContainer($containerId);
        }

        // Perbarui data sesi di database
        $session->update([
            'status' => in_array($status, ['expired', 'terminated']) ? $status : 'terminated',
            'ended_at' => Carbon::now(),
            'ws_token' => null,
        ]);

        return true;
    }

    /**
     * Membersihkan semua sesi yang telah melewati batas expires_at (FR-TERM-04).
     */
    public function cleanExpiredSessions(): int
    {
        $expiredSessions = TerminalSession::query()
            ->where('status', 'active')
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredSessions as $session) {
            $this->destroySession($session, 'expired');
            $count++;
        }

        return $count;
    }

    /**
     * Eksekusi perintah non-interaktif di dalam container sesi milik pengguna (FR-TERM-02, SEC-TERM-07).
     * Backend tidak pernah menjalankan perintah pengguna pada host/server utama.
     */
    public function executeCommand(TerminalSession $session, string $command): array
    {
        if (! $session->isActive()) {
            throw new RuntimeException('Sesi terminal sudah berakhir atau kedaluwarsa.');
        }

        // Perintah dieksekusi di dalam container sesi sebagai user student
        $cmd = [
            'docker', 'exec',
            '-u', 'student',
            '-w', '/home/student',
            $session->container_id,
            'bash', '-c', $command,
        ];

        $process = new Process($cmd);
        $process->setTimeout(15);
        $process->run();

        return [
            'exit_code' => $process->getExitCode() ?? 1,
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
    }

    /**
     * Menghapus container Docker secara paksa.
     */
    public function destroyContainer(string $containerId): bool
    {
        $process = new Process(['docker', 'rm', '-f', $containerId]);
        $process->setTimeout(15);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Memeriksa apakah container masih ada dan berjalan di Docker host.
     */
    public function isContainerRunning(string $containerId): bool
    {
        $process = new Process(['docker', 'inspect', '-f', '{{.State.Running}}', $containerId]);
        $process->setTimeout(5);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === 'true';
    }
}
