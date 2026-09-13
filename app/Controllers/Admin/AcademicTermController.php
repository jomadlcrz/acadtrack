<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Database;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use PDO;

class AcademicTermController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->query("
            SELECT at.*, 
                   COALESCE(at.school_year, ay.name, '2026-2027') as school_year_display,
                   ay.name as academic_year_name,
                   (SELECT COUNT(*) FROM sets s WHERE s.academic_term_id = at.id) as sets_count,
                   (SELECT COUNT(*) FROM subjects sub WHERE sub.academic_term_id = at.id) as subjects_count
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            ORDER BY COALESCE(at.school_year, ay.name) DESC, at.semester ASC
        ");
        $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = (new View())->render('admin.academic_terms.index', [
            'terms' => $terms,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $schoolYear = trim((string) $request->get('school_year', ''));
        $semesterRaw = trim((string) $request->get('semester', '1'));
        $setActive = !empty($request->get('set_active'));

        // Validate school year format: YYYY-YYYY
        if (!preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)) {
            $session->flash('error', 'School year must use format YYYY-YYYY, for example 2026-2027.');
            redirect('/admin/academic-terms');
            return;
        }

        $y1 = (int) $matches[1];
        $y2 = (int) $matches[2];
        if ($y2 !== $y1 + 1) {
            $session->flash('error', 'School year must use consecutive years (e.g., 2026-2027).');
            redirect('/admin/academic-terms');
            return;
        }

        // Normalize semester to int (1, 2, or 3 for Summer)
        $semester = match ($semesterRaw) {
            '1', '1st Semester' => 1,
            '2', '2nd Semester' => 2,
            '3', 'Summer' => 3,
            default => (int) $semesterRaw ?: 1,
        };

        $pdo = Database::getConnection();

        // 1. Ensure academic_years record exists
        $stmtYear = $pdo->prepare("SELECT id FROM academic_years WHERE name = :name LIMIT 1");
        $stmtYear->execute(['name' => $schoolYear]);
        $yearRow = $stmtYear->fetch(PDO::FETCH_ASSOC);

        if (!$yearRow) {
            $stmtInsertYear = $pdo->prepare("
                INSERT INTO academic_years (name, is_active, created_at, updated_at)
                VALUES (:name, 0, NOW(), NOW())
            ");
            $stmtInsertYear->execute(['name' => $schoolYear]);
            $yearId = (int) $pdo->lastInsertId();
        } else {
            $yearId = (int) $yearRow['id'];
        }

        // 2. Check if academic_terms already exists for this year and semester
        $stmtTerm = $pdo->prepare("
            SELECT id FROM academic_terms 
            WHERE (academic_year_id = :yid OR school_year = :syear) AND semester = :sem
            LIMIT 1
        ");
        $stmtTerm->execute(['yid' => $yearId, 'syear' => $schoolYear, 'sem' => $semester]);
        $termRow = $stmtTerm->fetch(PDO::FETCH_ASSOC);

        if ($termRow) {
            $session->flash('error', "Academic term for {$schoolYear} semester {$semester} already exists.");
            redirect('/admin/academic-terms');
            return;
        }

        if ($setActive) {
            $pdo->exec("UPDATE academic_terms SET is_active = 0");
            $pdo->exec("UPDATE academic_years SET is_active = 0");
            $pdo->prepare("UPDATE academic_years SET is_active = 1 WHERE id = :id")->execute(['id' => $yearId]);
        }

        $stmtInsertTerm = $pdo->prepare("
            INSERT INTO academic_terms (academic_year_id, school_year, semester, is_active, is_archived, created_at, updated_at)
            VALUES (:yid, :syear, :sem, :active, 0, NOW(), NOW())
        ");
        $stmtInsertTerm->execute([
            'yid' => $yearId,
            'syear' => $schoolYear,
            'sem' => $semester,
            'active' => $setActive ? 1 : 0,
        ]);

        $session->flash('success', "Academic term for {$schoolYear} successfully created.");
        redirect('/admin/academic-terms');
    }

    public function toggleActive(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM academic_terms WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $termId]);
        $term = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$term) {
            $session->flash('error', 'Academic term not found.');
            redirect('/admin/academic-terms');
            return;
        }

        // Set all inactive then activate this term
        $pdo->exec("UPDATE academic_terms SET is_active = 0");
        $pdo->exec("UPDATE academic_years SET is_active = 0");

        $pdo->prepare("UPDATE academic_terms SET is_active = 1, is_archived = 0 WHERE id = :id")->execute(['id' => $termId]);
        if (!empty($term['academic_year_id'])) {
            $pdo->prepare("UPDATE academic_years SET is_active = 1 WHERE id = :id")->execute(['id' => $term['academic_year_id']]);
        }

        $session->flash('success', 'Academic term set as active.');
        redirect('/admin/academic-terms');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $pdo = Database::getConnection();

        $pdo->prepare("
            UPDATE academic_terms 
            SET is_archived = 1, is_active = 0, archived_at = NOW() 
            WHERE id = :id
        ")->execute(['id' => $termId]);

        $session->flash('success', 'Academic term archived.');
        redirect('/admin/academic-terms');
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $pdo = Database::getConnection();

        $pdo->prepare("
            UPDATE academic_terms 
            SET is_archived = 0, archived_at = NULL 
            WHERE id = :id
        ")->execute(['id' => $termId]);

        $session->flash('success', 'Academic term restored.');
        redirect('/admin/academic-terms');
    }
}
