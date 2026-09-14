<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GradingSheet extends Model
{
    protected $table = 'grading_sheets';

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function gradingPeriod()
    {
        return $this->belongsTo(GradingPeriod::class, 'grading_period_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_FINALIZED = 'FINALIZED';
    const STATUS_RETURNED = 'RETURNED';

    public static function getByFaculty(int $facultyId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT gs.*, 
                   s.subject_code, s.subject_code as code, s.subject_code as subject_code,
                   s.descriptive_title, s.descriptive_title as name, s.descriptive_title as subject_name,
                   gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            WHERE gs.faculty_id = :faculty_id AND gs.academic_term_id = :academic_term_id
            ORDER BY gs.updated_at DESC
        ");
        $stmt->execute(['faculty_id' => $facultyId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function getPendingReview(int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT gs.*, 
                   s.subject_code, s.subject_code as code, s.subject_code as subject_code,
                   s.descriptive_title, s.descriptive_title as name, s.descriptive_title as subject_name,
                   gp.name as period_name, fd.first_name, fd.last_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            WHERE gs.academic_term_id = :academic_term_id 
              AND gs.status IN ('SUBMITTED', 'UNDER_REVIEW')
            ORDER BY gs.submitted_at DESC
        ");
        $stmt->execute(['academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function findByComposite(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId): ?array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM grading_sheets
            WHERE faculty_id = :faculty_id AND subject_id = :subject_id
              AND grading_period_id = :grading_period_id AND academic_term_id = :academic_term_id
            LIMIT 1
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findWithDetails(int $id): ?array
    {
        $stmt = self::db()->prepare("
            SELECT gs.*, 
                   s.subject_code, s.subject_code as code, s.descriptive_title as subject_name, s.nature as subject_nature, s.year_level, s.semester,
                   gp.name as period_name,
                   fd.first_name as faculty_first_name, fd.last_name as faculty_last_name, u.email as faculty_email,
                   ay.school_year as academic_year_name,
                   COALESCE(app_ad.first_name, app_fd.first_name, '') as approver_first_name,
                   COALESCE(app_ad.last_name, app_fd.last_name, '') as approver_last_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            JOIN academic_terms at ON gs.academic_term_id = at.id
            JOIN academic_years ay ON at.academic_year_id = ay.id
            LEFT JOIN admin_details app_ad ON gs.approved_by = app_ad.user_id
            LEFT JOIN faculty_details app_fd ON gs.approved_by = app_fd.user_id
            WHERE gs.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getAllWithDetails(int $academicTermId, ?string $status = null): array
    {
        $sql = "
            SELECT gs.*, s.subject_code, s.subject_code as code, s.descriptive_title as subject_name, 
                   gp.name as period_name, fd.first_name, fd.last_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            WHERE gs.academic_term_id = :academic_term_id
        ";
        $params = ['academic_term_id' => $academicTermId];

        if ($status !== null) {
            $sql .= " AND gs.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY gs.updated_at DESC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function paginateWithDetails(int $academicTermId, ?string $status = null, int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $whereClauses = ['gs.academic_term_id = :academic_term_id'];
        $params = ['academic_term_id' => $academicTermId];

        if ($status !== null) {
            $whereClauses[] = 'gs.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $whereClauses[] = "CONCAT(s.subject_code, ' ', s.descriptive_title, ' ', COALESCE(fd.first_name, ''), ' ', COALESCE(fd.last_name, ''), ' ', gp.name) LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $whereClauses);

        $countSql = "
            SELECT COUNT(*)
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            WHERE {$whereSql}
        ";
        $stmt = self::db()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $dataSql = "
            SELECT gs.*, s.subject_code, s.subject_code as code, s.descriptive_title as subject_name, 
                   gp.name as period_name, fd.first_name, fd.last_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            WHERE {$whereSql}
            ORDER BY gs.updated_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = self::db()->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'per_page' => $perPage,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public static function updateStatus(int $id, string $status): bool
    {
        return (bool) self::where('id', $id)->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
