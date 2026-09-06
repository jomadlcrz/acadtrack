<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicTerm extends Model
{
    protected static function table(): string
    {
        return 'academic_terms';
    }

    public static function getActive(): ?array
    {
        $stmt = self::db()->query("
            SELECT at.*, ay.name as academic_year_name
            FROM academic_terms at
            JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.is_active = 1
            LIMIT 1
        ");
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getAll(): array
    {
        $stmt = self::db()->query("
            SELECT at.*, ay.name as academic_year_name
            FROM academic_terms at
            JOIN academic_years ay ON at.academic_year_id = ay.id
            ORDER BY ay.name DESC, at.semester
        ");
        return $stmt->fetchAll();
    }
}
