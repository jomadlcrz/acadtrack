<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\GradingSheet;

class GradingSheetRepository
{
    public function findById(int $id): ?array
    {
        return GradingSheet::find($id);
    }

    public function findByComposite(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId): ?array
    {
        return GradingSheet::findByComposite($facultyId, $subjectId, $gradingPeriodId, $academicTermId);
    }

    public function getByFaculty(int $facultyId, int $academicTermId): array
    {
        return GradingSheet::getByFaculty($facultyId, $academicTermId);
    }

    public function getPendingReview(int $academicTermId): array
    {
        return GradingSheet::getPendingReview($academicTermId);
    }

    public function create(array $data): int
    {
        $data['status'] = $data['status'] ?? GradingSheet::STATUS_DRAFT;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return GradingSheet::create($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return GradingSheet::updateStatus($id, $status);
    }

    public function submit(int $id): bool
    {
        return GradingSheet::update($id, [
            'status' => GradingSheet::STATUS_SUBMITTED,
            'submitted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function approve(int $id): bool
    {
        return GradingSheet::update($id, [
            'status' => GradingSheet::STATUS_APPROVED,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function returnToFaculty(int $id): bool
    {
        return GradingSheet::update($id, [
            'status' => GradingSheet::STATUS_RETURNED,
            'returned_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
