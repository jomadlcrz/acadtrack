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

    public function delete(int $id): bool
    {
        return (bool) Subject::destroy($id);
    }

    public function getByDean(int $academicTermId): array
    {
        return Subject::getByDean($academicTermId);
    }

    public function getActive(): array
    {
        return Subject::getActive();
    }
}
