<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Subject;

class SubjectRepository
{
    public function findById(int $id): ?array
    {
        return Subject::find($id);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return Subject::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Subject::update($id, $data);
    }

    public function delete(int $id): bool
    {
        return Subject::delete($id);
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
