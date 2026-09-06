<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\GradeRepository;
use App\Repositories\GradingSheetRepository;
use App\Models\GradingSheet;

class GradingService
{
    private GradeRepository $gradeRepository;
    private GradingSheetRepository $gradingSheetRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
        $this->gradingSheetRepository = new GradingSheetRepository();
    }

    public function saveGrades(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId, array $studentGrades): void
    {
        foreach ($studentGrades as $studentId => $grade) {
            $gradeValue = (float) $grade;
            $this->gradeRepository->saveGrade(
                (int) $studentId,
                $subjectId,
                $gradingPeriodId,
                $academicTermId,
                $gradeValue
            );
        }

        $this->ensureGradingSheet($facultyId, $subjectId, $gradingPeriodId, $academicTermId);
    }

    private function ensureGradingSheet(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId): void
    {
        $existing = $this->gradingSheetRepository->findByComposite(
            $facultyId,
            $subjectId,
            $gradingPeriodId,
            $academicTermId
        );

        if (!$existing) {
            $this->gradingSheetRepository->create([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'grading_period_id' => $gradingPeriodId,
                'academic_term_id' => $academicTermId,
                'status' => GradingSheet::STATUS_DRAFT,
            ]);
        }
    }

    public function submitGradingSheet(int $gradingSheetId): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        if ($sheet['status'] !== GradingSheet::STATUS_DRAFT && $sheet['status'] !== GradingSheet::STATUS_RETURNED) {
            throw new \RuntimeException("Only DRAFT or RETURNED sheets can be submitted.");
        }

        $this->gradingSheetRepository->submit($gradingSheetId);
    }

    public function approveGradingSheet(int $gradingSheetId): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        if ($sheet['status'] !== GradingSheet::STATUS_SUBMITTED && $sheet['status'] !== GradingSheet::STATUS_UNDER_REVIEW) {
            throw new \RuntimeException("Sheet must be SUBMITTED or UNDER_REVIEW to approve.");
        }

        $this->gradingSheetRepository->approve($gradingSheetId);
    }

    public function returnGradingSheet(int $gradingSheetId): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $this->gradingSheetRepository->returnToFaculty($gradingSheetId);
    }

    public function getGradesByStudent(int $studentId, int $academicTermId): array
    {
        return $this->gradeRepository->getByStudent($studentId, $academicTermId);
    }

    public function getGradesBySubjectAndPeriod(int $subjectId, int $gradingPeriodId, int $academicTermId): array
    {
        return $this->gradeRepository->getBySubjectAndPeriod($subjectId, $gradingPeriodId, $academicTermId);
    }

    public function calculateFinalGrade(array $grades): float
    {
        if (empty($grades)) {
            return 0.0;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            $weight = $grade['weight'] ?? 1;
            $weightedSum += $grade['grade'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
    }
}
