<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $fillable = [
        'program_id',
        'subject_code',
        'descriptive_title',
        'units',
        'subject_type',
        'code',
        'name',
        'nature',
        'year_level',
        'semester',
        'academic_term_id',
        'is_archived',
        'archived_at',
        'created_at',
        'updated_at',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function prerequisites()
    {
        return $this->belongsToMany(Subject::class, 'prerequisites', 'subject_id', 'prerequisite_subject_id');
    }

    public function dependentSubjects()
    {
        return $this->belongsToMany(Subject::class, 'prerequisites', 'prerequisite_subject_id', 'subject_id');
    }

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['subject_code'] ?? null;
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['subject_code'] = $value;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['descriptive_title'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['descriptive_title'] = $value;
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function gradingSetting()
    {
        return $this->hasOne(GradingSetting::class, 'subject_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'subject_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'subject_id');
    }

    public function gradingSheets()
    {
        return $this->hasMany(GradingSheet::class, 'subject_id');
    }

    public function archive(): bool
    {
        return (bool) $this->update([
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function restore(): bool
    {
        return (bool) $this->update([
            'is_archived' => 0,
            'archived_at' => null,
        ]);
    }

    public function hasDependentRecords(): bool
    {
        $subjectId = (int) $this->id;
        $db = self::db();

        $stmt = $db->prepare("
            SELECT (
                (SELECT COUNT(*) FROM grades WHERE subject_id = :s1) +
                (SELECT COUNT(*) FROM enrollments WHERE subject_id = :s2) +
                (SELECT COUNT(*) FROM grading_sheets WHERE subject_id = :s3) +
                (SELECT COUNT(*) FROM faculty_subjects WHERE subject_id = :s4)
            ) as total_deps
        ");
        $stmt->execute([
            's1' => $subjectId,
            's2' => $subjectId,
            's3' => $subjectId,
            's4' => $subjectId,
        ]);
        $row = $stmt->fetch();
        return ((int) ($row['total_deps'] ?? 0)) > 0;
    }

    public static function getByDean(int $academicTermId, ?string $statusFilter = null, ?int $semester = null): array
    {
        $params = ['academic_term_id' => $academicTermId];
        $whereConditions = [];

        if ($semester !== null && $semester > 0) {
            $whereConditions[] = "s.academic_term_id = :academic_term_id2 AND s.semester = :semester";
            $params['academic_term_id2'] = $academicTermId;
            $params['semester'] = $semester;
        } else {
            $whereConditions[] = "(s.academic_term_id = :academic_term_id2 OR s.academic_term_id IN (
                SELECT at.id FROM academic_terms at WHERE at.academic_year_id = (
                    SELECT at2.academic_year_id FROM academic_terms at2 WHERE at2.id = :academic_term_id3
                )
            ))";
            $params['academic_term_id2'] = $academicTermId;
            $params['academic_term_id3'] = $academicTermId;
        }

        if ($statusFilter === 'active') {
            $whereConditions[] = "s.is_archived = 0";
        } elseif ($statusFilter === 'archived') {
            $whereConditions[] = "s.is_archived = 1";
        }

        $whereSql = implode(" AND ", $whereConditions);

        $stmt = self::db()->prepare("
            SELECT s.*, 
                   s.subject_code as code,
                   s.descriptive_title as name,
                   s.units,
                   s.subject_type,
                   COUNT(DISTINCT fs.faculty_id) as assigned_faculty_count,
                   (SELECT COUNT(*) FROM enrollments e WHERE e.subject_id = s.id) as enrolled_students_count,
                   (SELECT COUNT(*) FROM grades g WHERE g.subject_id = s.id) as grades_count
            FROM subjects s
            LEFT JOIN faculty_subjects fs ON fs.subject_id = s.id AND fs.academic_term_id = :academic_term_id
            WHERE {$whereSql}
            GROUP BY s.id
            ORDER BY s.is_archived ASC, s.subject_code ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getActive(): array
    {
        $stmt = self::db()->query("
            SELECT s.*, 
                   s.subject_code as code,
                   s.descriptive_title as name,
                   ay.school_year as academic_year_name
            FROM subjects s
            JOIN academic_terms at2 ON s.academic_term_id = at2.id
            JOIN academic_years ay ON at2.academic_year_id = ay.id
            WHERE at2.is_active = 1 AND s.is_archived = 0
            ORDER BY s.subject_code ASC
        ");
        return $stmt->fetchAll();
    }
}
