<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\TerminalSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ValidationService
{
    public function __construct(
        protected TerminalService $terminalService
    ) {}

    /**
     * Menilai jawaban latihan berdasarkan validation_type dan validation_config (SPEC Bagian 11).
     *
     * @param  User  $user  Pengguna yang sedang dinilai
     * @param  Exercise  $exercise  Latihan yang dikerjakan
     * @param  TerminalSession|null  $session  Sesi terminal aktif
     * @param  int  $unlockedHintsCount  Jumlah hint yang telah dibuka oleh pengguna
     * @return array{
     *     success: bool,
     *     is_correct: bool,
     *     score: int,
     *     feedback: string,
     *     attempt: ExerciseAttempt,
     *     checks: array<int, array{type: string, passed: bool, message: string}>
     * }
     */
    public function validate(
        User $user,
        Exercise $exercise,
        ?TerminalSession $session = null,
        int $unlockedHintsCount = 0
    ): array {
        // VAL-06 / D-08: Apabila tidak ada sesi terminal aktif, tolak dengan pesan jelas
        if (! $session || ! $session->isActive()) {
            $feedback = 'Tidak ada sesi terminal aktif. Silakan mulai sesi praktik terlebih dahulu.';

            // FR-EX-02 AC: Setiap penekanan tombol Periksa Jawaban membuat satu baris baru pada exercise_attempts
            $attempt = ExerciseAttempt::create([
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'terminal_session_id' => null,
                'is_correct' => false,
                'score' => 0,
                'feedback' => $feedback,
            ]);

            return [
                'success' => false,
                'is_correct' => false,
                'score' => 0,
                'feedback' => $feedback,
                'attempt' => $attempt,
                'checks' => [],
            ];
        }

        // VAL-01: Pemeriksaan dijalankan terhadap container sesi aktif milik pengguna yang dinilai
        if ($session->user_id !== $user->id) {
            throw new AuthorizationException('Tidak dapat menilai sesi terminal milik pengguna lain.');
        }

        $config = $exercise->validation_config ?? [];
        $checks = $config['checks'] ?? [];

        $checkResults = [];
        $passedCount = 0;
        $totalChecks = count($checks);

        foreach ($checks as $check) {
            $result = $this->evaluateCheck($session, $check);
            $checkResults[] = $result;

            if ($result['passed']) {
                $passedCount++;
            }
        }

        // Jawaban benar jika semua check lulus
        $isCorrect = ($totalChecks > 0 && $passedCount === $totalChecks);

        // VAL-03: Skor kalkulasi
        $isPartial = ! empty($config['partial']);
        if ($isCorrect) {
            $score = (int) $exercise->points;
        } elseif ($isPartial && $totalChecks > 0) {
            $score = (int) round(($passedCount / $totalChecks) * $exercise->points);
        } else {
            $score = 0;
        }

        // D-06: Bawaan hint tidak mengurangi skor, kecuali diatur pada validation_config (hint_penalty)
        if ($isCorrect && isset($config['hint_penalty']) && (int) $config['hint_penalty'] > 0) {
            $penalty = $unlockedHintsCount * (int) $config['hint_penalty'];
            $score = max(0, $score - $penalty);
        }

        // VAL-04: Feedback dalam Bahasa Indonesia yang ramah pemula
        $feedback = $this->buildFeedbackMessage($isCorrect, $passedCount, $totalChecks, $checkResults);

        // FR-EX-02 AC & VAL-05: Catat percobaan pada exercise_attempts
        $attempt = ExerciseAttempt::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'terminal_session_id' => $session->id,
            'is_correct' => $isCorrect,
            'score' => $score,
            'feedback' => $feedback,
        ]);

        return [
            'success' => true,
            'is_correct' => $isCorrect,
            'score' => $score,
            'feedback' => $feedback,
            'attempt' => $attempt,
            'checks' => $checkResults,
        ];
    }

    /**
     * Mengevaluasi satu check satuan di dalam container sesi (SPEC Bagian 11.2).
     *
     * @param  array<string, mixed>  $check
     * @return array{type: string, passed: bool, message: string}
     */
    protected function evaluateCheck(TerminalSession $session, array $check): array
    {
        $type = $check['type'] ?? '';

        switch ($type) {
            case 'file_exists':
                $path = (string) ($check['path'] ?? '');
                // test -f memastikan berkas reguler ada (bukan direktori)
                $cmd = 'test -f ' . escapeshellarg($path);
                $res = $this->terminalService->executeCommand($session, $cmd);
                $passed = ($res['exit_code'] === 0);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Berkas '{$path}' berhasil ditemukan."
                        : "Berkas '{$path}' belum ditemukan atau bukan berkas reguler.",
                ];

            case 'directory_exists':
                $path = (string) ($check['path'] ?? '');
                $cmd = 'test -d ' . escapeshellarg($path);
                $res = $this->terminalService->executeCommand($session, $cmd);
                $passed = ($res['exit_code'] === 0);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Direktori '{$path}' berhasil ditemukan."
                        : "Direktori '{$path}' belum ditemukan.",
                ];

            case 'file_contains':
                $path = (string) ($check['path'] ?? '');
                $text = (string) ($check['text'] ?? '');

                // Cek dulu keberadaan berkas
                $checkFile = $this->terminalService->executeCommand($session, 'test -f ' . escapeshellarg($path));
                if ($checkFile['exit_code'] !== 0) {
                    return [
                        'type' => $type,
                        'passed' => false,
                        'message' => "Berkas '{$path}' tidak ditemukan.",
                    ];
                }

                $cmd = 'grep -F -q ' . escapeshellarg($text) . ' ' . escapeshellarg($path);
                $res = $this->terminalService->executeCommand($session, $cmd);
                $passed = ($res['exit_code'] === 0);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Berkas '{$path}' memuat teks yang sesuai."
                        : "Berkas '{$path}' belum memuat teks yang diharapkan.",
                ];

            case 'file_permission':
                $path = (string) ($check['path'] ?? '');
                $expectedMode = (string) ($check['mode'] ?? '');

                $checkFile = $this->terminalService->executeCommand($session, 'test -e ' . escapeshellarg($path));
                if ($checkFile['exit_code'] !== 0) {
                    return [
                        'type' => $type,
                        'passed' => false,
                        'message' => "Target '{$path}' tidak ditemukan untuk pemeriksaan permission.",
                    ];
                }

                $cmd = 'stat -c "%a" ' . escapeshellarg($path);
                $res = $this->terminalService->executeCommand($session, $cmd);
                $actualMode = ltrim(trim($res['output']), '0');
                $normExpected = ltrim(trim($expectedMode), '0');

                $passed = ($actualMode === $normExpected);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Izin akses (permission) '{$path}' sesuai ({$expectedMode})."
                        : "Izin akses '{$path}' belum sesuai (diharapkan: {$expectedMode}, saat ini: {$actualMode}).",
                ];

            case 'command_output_contains':
                // VAL-02: Perintah pemeriksa ditetapkan oleh administrator pada validation_config
                $command = (string) ($check['command'] ?? '');
                $text = (string) ($check['text'] ?? '');

                $res = $this->terminalService->executeCommand($session, $command);
                $output = $res['output'] . ($res['exit_code'] !== 0 ? ' ' . $res['error'] : '');
                $passed = str_contains($output, $text);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Keluaran perintah menghasilkan teks yang sesuai."
                        : "Keluaran perintah belum memuat teks yang diharapkan.",
                ];

            case 'command_output_equals':
                // VAL-02: Perintah pemeriksa ditetapkan oleh administrator pada validation_config
                $command = (string) ($check['command'] ?? '');
                $text = (string) ($check['text'] ?? '');

                $res = $this->terminalService->executeCommand($session, $command);
                $actualOutput = trim($res['output']);
                $expectedOutput = trim($text);

                $passed = ($actualOutput === $expectedOutput);

                return [
                    'type' => $type,
                    'passed' => $passed,
                    'message' => $passed
                        ? "Keluaran perintah sesuai dengan yang diharapkan."
                        : "Keluaran perintah belum sesuai dengan yang diharapkan.",
                ];

            default:
                return [
                    'type' => $type,
                    'passed' => false,
                    'message' => "Jenis pemeriksaan '{$type}' tidak dikenali.",
                ];
        }
    }

    /**
     * Membangun teks feedback dalam Bahasa Indonesia ramah pemula (VAL-04).
     *
     * @param  array<int, array{type: string, passed: bool, message: string}>  $checkResults
     */
    protected function buildFeedbackMessage(bool $isCorrect, int $passedCount, int $totalChecks, array $checkResults): string
    {
        if ($isCorrect) {
            $header = "Selamat! Seluruh pemeriksaan berhasil dipenuhi ({$passedCount}/{$totalChecks}).";
        } else {
            $header = "Jawaban belum lengkap atau belum sesuai. {$passedCount} dari {$totalChecks} pemeriksaan terpenuhi.";
        }

        $lines = [$header];
        foreach ($checkResults as $check) {
            $icon = $check['passed'] ? '✓' : '✗';
            $lines[] = "{$icon} {$check['message']}";
        }

        return implode("\n", $lines);
    }
}
