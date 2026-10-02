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
            $isEnrolled = \Illuminate\Database\Capsule\Manager::table('enrollments')
                ->where('student_id', $student->id)
                ->where('subject_id', $subjectId)
                ->where('academic_term_id', $termId)
                ->exists();

            if ($isEnrolled) {
                $evalService = new EvaluationService();
                $passData = $evalService->getDigitalPass((int) $student->id, $subjectId, $termId);
                if ($passData) {
                    $isValid = true;
                }
            }
        }

        $subject = $subjectId > 0 ? Subject::find($subjectId) : null;
        $academicTerm = $termId > 0 ? AcademicTerm::find($termId) : null;
        $termName = '';
        if ($academicTerm) {
            $semText = $academicTerm->semester == 1 ? '1st Semester' : ($academicTerm->semester == 2 ? '2nd Semester' : 'Summer');
            $termName = $semText . ' ' . ($academicTerm->school_year ?? '');
        } elseif ($termId > 0) {
            $termName = 'Academic Term #' . $termId;
        }

        $studentUser = $student ? $student->user : null;
        $studentName = $studentUser ? trim(($studentUser->first_name ?? '') . ' ' . ($studentUser->last_name ?? '')) : '';
        if ($studentName === '' && $studentUser) {
            $studentName = (string) ($studentUser->name ?? '');
        }

        $html = (new View())->render('verification.student-pass', [
            'isValid' => $isValid,
            'passData' => $passData,
            'idParam' => $idParam,
            'student' => $student,
            'studentName' => $studentName,
            'subject' => $subject,
            'subjectId' => $subjectId,
            'academicTerm' => $academicTerm,
            'termName' => $termName,
            'termId' => $termId,
        ]);

        $response->html($html);
    }
}
