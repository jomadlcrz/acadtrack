<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Subject;

class SubjectRepository
{
    public function findById(int $id): ?array
    {
        $subject = Subject::find($id);
        return $subject ? $subject->toArray() : null;
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $subject = Subject::create($data);
        return (int) $subject->id;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return (bool) Subject::where('id', $id)->update($data);
    }

    public function archive(int $id): bool
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return false;
        }
        return $subject->archive();
    }

    public function restore(int $id): bool
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return false;
        }
        return $subject->restore();
    }

    public function getByDean(int $academicTermId, ?string $statusFilter = null, ?int $semester = null): array
    {
        return Subject::getByDean($academicTermId, $statusFilter, $semester);
    }

    public function getActive(): array
    {
        return Subject::getActive();
    }
}
