<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GradeHistoryLog extends Model
{
    protected $table = 'grade_history_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'grade_id',
        'student_id',
        'old_score',
        'new_score',
        'action_performed',
        'changed_at',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public static function getByStudent(int $studentId): array
    {
        $stmt = self::db()->prepare("
            SELECT ghl.*, 
                   s.subject_code, s.subject_code as code, s.subject_code as subject_code, 
                   s.descriptive_title, s.descriptive_title as name, s.descriptive_title as subject_name
            FROM grade_history_log ghl
            LEFT JOIN grades g ON ghl.grade_id = g.id
            LEFT JOIN subjects s ON g.subject_id = s.id
            WHERE ghl.student_id = :student_id
            ORDER BY ghl.changed_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function getByGrade(int $gradeId): array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM grade_history_log
            WHERE grade_id = :grade_id
            ORDER BY changed_at DESC
        ");
        $stmt->execute(['grade_id' => $gradeId]);
        return $stmt->fetchAll();
    }
}
