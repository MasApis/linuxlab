<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AchievementController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Menampilkan halaman Pencapaian (lencana & sertifikat).
     * Sesuai FR-PROG-05, FR-CERT-03, dan FR-CERT-05.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Seluruh master lencana beserta status perolehan pengguna
        $userBadges = $user->userBadges->keyBy('badge_id');

        $badges = Badge::query()
            ->orderBy('id')
            ->get()
            ->map(function (Badge $badge) use ($userBadges) {
                $userBadge = $userBadges->get($badge->id);
                $isEarned = (bool) $userBadge;

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'criteria_type' => $badge->criteria_type,
                    'criteria_value' => $badge->criteria_value,
                    'is_earned' => $isEarned,
                    'awarded_at' => $isEarned && $userBadge->awarded_at ? $userBadge->awarded_at->translatedFormat('d M Y') : null,
                ];
            });

        // 2. Data Sertifikat (FR-CERT-03 dan FR-CERT-05)
        $certificate = Certificate::where('user_id', $user->id)->first();
        $isEligible = $this->certificateService->isEligible($user);
        $uncompletedModules = $this->certificateService->getUncompletedModules($user);

        $certificateData = null;
        if ($certificate) {
            $certificateData = [
                'recipient_name' => $certificate->recipient_name,
                'certificate_code' => $certificate->certificate_code,
                'issued_at' => $certificate->issued_at->translatedFormat('d F Y'),
                'download_url' => route('certificates.download'),
                'verify_url' => route('certificates.verify', ['code' => $certificate->certificate_code]),
            ];
        }

        return Inertia::render('Achievements/Index', [
            'badges' => $badges,
            'certificate' => $certificateData,
            'isEligible' => $isEligible,
            'uncompletedModules' => $uncompletedModules,
        ]);
    }
}
