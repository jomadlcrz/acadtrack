<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\GradeRepository;

class GradeService
{
    private GradeRepository $gradeRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
    }

    public function getStudentGrades(int $studentId, int $academicTermId): array
    {
        return $this->gradeRepository->getByStudent($studentId, $academicTermId);
    }

    public function getGradeSummary(int $studentId, int $academicTermId): array
    {
        $grades = $this->getStudentGrades($studentId, $academicTermId);
        $summary = [];

        foreach ($grades as $grade) {
            $subjectCode = $grade['subject_code'];
            if (!isset($summary[$subjectCode])) {
                $summary[$subjectCode] = [
                    'subject_id' => (int)($grade['subject_id'] ?? 0),
                    'subject_code' => $subjectCode,
                    'subject_name' => $grade['subject_name'],
                    'periods' => [],
                ];
            }
            $summary[$subjectCode]['periods'][$grade['period_name']] = $grade['grade'];
        }

        return $summary;
    }
}
