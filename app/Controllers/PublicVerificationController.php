<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Student;
use App\Models\StudentDetail;
use App\Models\Subject;
use App\Models\AcademicTerm;
use App\Services\EvaluationService;

class PublicVerificationController
{
    public function verifyStudentPass(Request $request, Response $response): void
    {
        $idParam = trim((string) $request->get('id', ''));
        $subjectId = (int) $request->get('sub', 0);
        $termId = (int) $request->get('term', 0);

        $student = null;
        if (!empty($idParam)) {
            // Find by student number first
            $detail = StudentDetail::where('student_number', $idParam)->first();
            if ($detail) {
                $student = Student::where('user_id', $detail->user_id)->first();
            } else {
                // Try numeric student ID
                $num = (int) preg_replace('/\D/', '', $idParam);
                if ($num > 0) {
                    $student = Student::find($num);
                }
            }
        }

        $passData = null;
        $isValid = false;

        if ($student && $subjectId > 0 && $termId > 0) {
            $evalService = new EvaluationService();
            $passData = $evalService->getDigitalPass((int) $student->id, $subjectId, $termId);
            if ($passData) {
                $isValid = true;
            }
        }

        $html = (new View())->render('verification.student-pass', [
            'isValid' => $isValid,
            'passData' => $passData,
            'idParam' => $idParam,
        ]);

        $response->html($html);
    }
}
