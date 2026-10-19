<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Memeriksa apakah pengguna berhak mendapatkan sertifikat.
     * Sesuai FR-CERT-01: pengguna dengan role 'user' yang telah menyelesaikan
     * seluruh modul yang diterbitkan (is_published = true).
     */
    public function isEligible(User $user): bool
    {
        if ($user->role !== 'user') {
            return false;
        }

        $publishedModules = Module::query()
            ->where('is_published', true)
            ->get();

        if ($publishedModules->isEmpty()) {
            return false;
        }

        $completedCount = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('module_id', $publishedModules->pluck('id'))
            ->where('status', 'completed')
            ->count();

        return $completedCount >= $publishedModules->count();
    }

    /**
     * Menerbitkan sertifikat otomatis tepat satu kali untuk pengguna (FR-CERT-01 s.d. 02).
     */
    public function issueCertificateIfEligible(User $user): ?Certificate
    {
        if (! $this->isEligible($user)) {
            return null;
        }

        // Jika sudah memiliki sertifikat, jangan terbitkan ulang (UNIQUE user_id)
        $existing = Certificate::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $code = $this->generateUniqueCertificateCode();

        return Certificate::create([
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'certificate_code' => $code,
            'issued_at' => now(),
        ]);
    }

    /**
     * Menghasilkan kode sertifikat berformat: LLAB-<tahun>-<6 karakter acak huruf besar & angka> (FR-CERT-02).
     */
    public function generateUniqueCertificateCode(): string
    {
        $year = now()->format('Y');

        do {
            $randomString = strtoupper(Str::random(6));
            $code = "LLAB-{$year}-{$randomString}";
        } while (Certificate::where('certificate_code', $code)->exists());

        return $code;
    }

    /**
     * Mendapatkan daftar modul yang belum selesai untuk kartu sertifikat terkunci (FR-CERT-05).
     *
     * @return array<int, string>
     */
    public function getUncompletedModules(User $user): array
    {
        $publishedModules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        $completedModuleIds = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('module_id')
            ->all();

        return $publishedModules
            ->filter(fn ($module) => ! in_array($module->id, $completedModuleIds, true))
            ->pluck('title')
            ->values()
            ->all();
    }

    /**
     * Memverifikasi keabsahan sertifikat berdasarkan kodenya (FR-CERT-04).
     *
     * @return array{
     *     status: 'valid'|'not_found',
     *     certificate?: Certificate,
     *     recipient_name?: string,
     *     issued_at?: string,
     *     certificate_code?: string,
     *     completed_modules?: array<int, string>
     * }
     */
    public function verifyCertificate(string $code): array
    {
        $trimmedCode = trim($code);
        $certificate = Certificate::with('user')->where('certificate_code', $trimmedCode)->first();

        if (! $certificate) {
            return [
                'status' => 'not_found',
            ];
        }

        // Ambil daftar modul yang diterbitkan
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->pluck('title')
            ->all();

        return [
            'status' => 'valid',
            'certificate' => $certificate,
            'recipient_name' => $certificate->recipient_name,
            'issued_at' => $certificate->issued_at->translatedFormat('d F Y'),
            'certificate_code' => $certificate->certificate_code,
            'completed_modules' => $modules,
        ];
    }

    /**
     * Menghasilkan dokumen PDF untuk sertifikat (FR-CERT-03).
     */
    public function generatePdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->pluck('title')
            ->all();

        $signatoryName = config('services.certificate.signatory_name') ?: 'Koordinator LinuxLab';
        $signatoryTitle = config('services.certificate.signatory_title') ?: 'Dosen Pengampu';

        $data = [
            'certificate' => $certificate,
            'recipientName' => $certificate->recipient_name,
            'certificateCode' => $certificate->certificate_code,
            'issuedAt' => $certificate->issued_at->translatedFormat('d F Y'),
            'modules' => $modules,
            'signatoryName' => $signatoryName,
            'signatoryTitle' => $signatoryTitle,
        ];

        return Pdf::loadView('certificates.pdf', $data)
            ->setPaper('a4', 'landscape');
    }

    /**
     * Mengambil daftar peringkat leaderboard publik sesuai SPEC Bagian 6.H (FR-LEAD-01 s.d. 04).
     * Dihitung dari skor terbaik per latihan, hanya akun role 'user' aktif, mengabaikan admin dan email.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function getLeaderboard()
    {
        $bestScores = \Illuminate\Support\Facades\DB::table('exercise_attempts')
            ->select('user_id', 'exercise_id', \Illuminate\Support\Facades\DB::raw('MAX(score) as max_score'), \Illuminate\Support\Facades\DB::raw('MIN(created_at) as first_correct_at'))
            ->where('is_correct', true)
            ->groupBy('user_id', 'exercise_id');

        $userStats = \Illuminate\Support\Facades\DB::query()->fromSub($bestScores, 'ea')
            ->select(
                'ea.user_id',
                \Illuminate\Support\Facades\DB::raw('CAST(SUM(ea.max_score) AS UNSIGNED) as total_score'),
                \Illuminate\Support\Facades\DB::raw('COUNT(ea.exercise_id) as correct_exercises_count'),
                \Illuminate\Support\Facades\DB::raw('MIN(ea.first_correct_at) as first_correct_at')
            )
            ->groupBy('ea.user_id');

        $rows = \Illuminate\Support\Facades\DB::table('users')
            ->joinSub($userStats, 'stats', 'users.id', '=', 'stats.user_id')
            ->where('users.role', 'user')
            ->where('users.is_active', true)
            ->select(
                'users.id as user_id',
                'users.name',
                'users.username',
                'stats.total_score',
                'stats.correct_exercises_count',
                'stats.first_correct_at',
                \Illuminate\Support\Facades\DB::raw('(SELECT COUNT(*) FROM module_progress mp WHERE mp.user_id = users.id AND mp.status = "completed") as completed_modules_count'),
                \Illuminate\Support\Facades\DB::raw('(SELECT COUNT(*) FROM user_badges ub WHERE ub.user_id = users.id) as badges_count')
            )
            ->orderByDesc('stats.total_score')
            ->orderByDesc('stats.correct_exercises_count')
            ->orderBy('stats.first_correct_at', 'asc')
            ->get();

        return $rows->values()->map(function ($row, $index) {
            $row->rank = $index + 1;
            $row->total_score = (int) $row->total_score;
            $row->correct_exercises_count = (int) $row->correct_exercises_count;
            $row->completed_modules_count = (int) $row->completed_modules_count;
            $row->badges_count = (int) $row->badges_count;
            return $row;
        });
    }

    /**
     * Mendapatkan posisi peringkat pengguna saat ini (FR-LEAD-03).
     * Mengembalikan null jika pengguna belum masuk peringkat (belum menyelesaikan latihan / admin).
     */
    public function getUserRank(User $user): ?int
    {
        if ($user->role !== 'user' || ! $user->is_active) {
            return null;
        }

        $leaderboard = $this->getLeaderboard();
        $userRow = $leaderboard->firstWhere('user_id', $user->id);

        return $userRow ? (int) $userRow->rank : null;
    }
}
