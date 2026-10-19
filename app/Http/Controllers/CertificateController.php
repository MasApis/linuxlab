<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Mengunduh sertifikat milik pengguna sendiri sebagai dokumen PDF (FR-CERT-03).
     * Route: GET /certificates/download (auth required)
     */
    public function download(Request $request): HttpResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $certificate = Certificate::where('user_id', $user->id)->first();

        if (! $certificate) {
            abort(403, 'Anda belum memiliki sertifikat penyelesaian.');
        }

        $pdf = $this->certificateService->generatePdf($certificate);

        return $pdf->download("sertifikat-linuxlab-{$certificate->certificate_code}.pdf");
    }

    /**
     * Memverifikasi keabsahan kode sertifikat secara publik (FR-CERT-04).
     * Route: GET /certificates/verify/{code?} (publik)
     */
    public function verify(Request $request, ?string $code = null): Response
    {
        $searchCode = $code ?: $request->query('code');
        $verificationResult = null;

        if ($searchCode) {
            $verificationResult = $this->certificateService->verifyCertificate($searchCode);
        }

        return Inertia::render('Certificates/Verify', [
            'code' => $searchCode,
            'result' => $verificationResult,
        ]);
    }
}
