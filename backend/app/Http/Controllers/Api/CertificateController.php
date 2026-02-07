<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function checkCertificate(Request $request, $courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            $user = $request->user();

            $certificate = $this->certificateService->checkAndGenerateCertificate($user, $course);

            if (! $certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not completed yet',
                    'data' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $certificate->load('course'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function myCertificates(Request $request)
    {
        $certificates = $this->certificateService->getUserCertificates($request->user());

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ]);
    }

    public function verifyCertificate(Request $request, string $certificateNumber)
    {
        try {
            $certificate = $this->certificateService->getCertificateByNumber($certificateNumber);

            return response()->json([
                'success' => true,
                'valid' => true,
                'data' => $certificate->load(['user', 'course.instructor']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Certificate not found',
            ], 404);
        }
    }

    public function downloadCertificate(Request $request, string $certificateNumber)
    {
        try {
            $certificate = $this->certificateService->getCertificateByNumber($certificateNumber);

            // Verify ownership or allow public access for verification
            if ($request->user() && $request->user()->id !== $certificate->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // In a real implementation, this would generate and return a PDF
            // For now, we'll return the certificate data
            return response()->json([
                'success' => true,
                'data' => $certificate->load(['user', 'course.instructor']),
                'download_url' => $certificate->certificate_url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
