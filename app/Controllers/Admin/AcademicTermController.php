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
        $activeTab = trim((string) $request->get('tab', 'school-years'));
        if (!in_array($activeTab, ['school-years', 'semesters', 'closure'], true)) {
            $activeTab = 'school-years';
        }

        // 1. Fetch School Years with counts & calendar status (matching reference SchoolYearTable)
        $stmtYears = $pdo->query("
            SELECT ay.id, ay.school_year, ay.is_active, ay.created_at,
                   COUNT(DISTINCT at.id) as terms_count
            FROM academic_years ay
            LEFT JOIN academic_terms at ON at.academic_year_id = ay.id
            GROUP BY ay.id, ay.school_year, ay.is_active, ay.created_at
            ORDER BY ay.is_active DESC, ay.school_year DESC
        ");
        $schoolYears = $stmtYears->fetchAll(PDO::FETCH_ASSOC);

        $currentYearInt = (int) date('Y');
        $currentMonth = (int) date('n');

        foreach ($schoolYears as &$sy) {
            $syName = $sy['school_year'];
            if (preg_match('/^(\d{4})-(\d{4})$/', $syName, $m)) {
                $startY = (int) $m[1];
                $endY = (int) $m[2];
                if ($currentYearInt < $startY) {
                    $sy['calendar_status'] = 'Upcoming';
                } elseif ($currentYearInt > $endY || ($currentYearInt === $endY && $currentMonth >= 6)) {
                    $sy['calendar_status'] = 'Ended';
                } else {
                    $sy['calendar_status'] = 'Ongoing';
                }
            } else {
                $sy['calendar_status'] = 'Ongoing';
            }
        }
        unset($sy);

        $ongoingCount = count(array_filter($schoolYears, fn($y) => $y['calendar_status'] === 'Ongoing'));
        $activeYear = array_values(array_filter($schoolYears, fn($y) => (int)$y['is_active'] === 1))[0] ?? ($schoolYears[0] ?? null);
        $currentYearName = $activeYear['school_year'] ?? '—';

        // 2. Global Reference Semesters (matching reference SemesterTable)
        $semesters = [
            [
                'id' => 1,
                'semester_number' => 1,
                'display_name' => '1st Semester',
                'description' => 'First semester of the academic year',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'semester_number' => 2,
                'display_name' => '2nd Semester',
                'description' => 'Second semester of the academic year',
                'status' => 'Active',
            ],
        ];

        // 3. Term Closures & Lifecycle Data (matching reference TermClosureTable)
        $closureService = new \App\Services\TermClosureService();
        $closures = $closureService->getClosures();

        // 4. Backward compatible terms list
        $stmtTerms = $pdo->query("
            SELECT at.*, 
                   COALESCE(at.school_year, ay.school_year, '2026-2027') as school_year_display,
                   ay.school_year as academic_year_name,
                   (SELECT COUNT(*) FROM sets s WHERE s.academic_term_id = at.id) as sets_count,
                   (SELECT COUNT(*) FROM subjects sub WHERE sub.academic_term_id = at.id) as subjects_count
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            ORDER BY COALESCE(at.school_year, ay.school_year) DESC, at.semester ASC
        ");
        $terms = $stmtTerms->fetchAll(PDO::FETCH_ASSOC);

        $html = (new View())->render('admin.academic_terms.index', [
            'activeTab' => $activeTab,
            'schoolYears' => $schoolYears,
            'currentYearName' => $currentYearName,
            'ongoingCount' => $ongoingCount,
            'semesters' => $semesters,
            'closures' => $closures,
            'terms' => $terms,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $schoolYear = trim((string) $request->input('school_year', ''));
        $semesterRaw = trim((string) $request->input('semester', '1'));
        $setActive = !empty($request->input('set_active'));

        // Normalize input (handle en-dash, em-dash, slashes, spacing, and auto-expand)
        $schoolYear = str_replace(['–', '—', '/'], '-', $schoolYear);
        $schoolYear = preg_replace('/\s*-\s*/', '-', $schoolYear);

        // Auto-expand 4-digit year (e.g., 2026 -> 2026-2027)
        if (preg_match('/^(\d{4})$/', $schoolYear, $singleMatch)) {
            $yStart = (int) $singleMatch[1];
            $schoolYear = $yStart . '-' . ($yStart + 1);
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $schoolYear, $twoDigitMatches)) {
            // Handle 2-digit end year (e.g., 2026-27 -> 2026-2027)
            $century = substr($twoDigitMatches[1], 0, 2);
            $schoolYear = $twoDigitMatches[1] . '-' . $century . $twoDigitMatches[2];
        }

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
        $stmtYear = $pdo->prepare("SELECT id FROM academic_years WHERE school_year = :name LIMIT 1");
        $stmtYear->execute(['name' => $schoolYear]);
        $yearRow = $stmtYear->fetch(PDO::FETCH_ASSOC);

        if (!$yearRow) {
            $stmtInsertYear = $pdo->prepare("
                INSERT INTO academic_years (school_year, is_active, created_at, updated_at)
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
        $newTermId = (int) $pdo->lastInsertId();

        // Automatically seed default 4 grading periods for the new term
        $stmtPeriods = $pdo->prepare("
            INSERT INTO grading_periods (academic_term_id, name, order_num, weight, is_current, is_closed)
            VALUES 
                (:tid1, 'Prelim', 1, 20.00, 1, 0),
                (:tid2, 'Midterm', 2, 20.00, 0, 0),
                (:tid3, 'Semi-Final', 3, 20.00, 0, 0),
                (:tid4, 'Final', 4, 40.00, 0, 0)
        ");
        $stmtPeriods->execute([
            'tid1' => $newTermId,
            'tid2' => $newTermId,
            'tid3' => $newTermId,
            'tid4' => $newTermId,
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
        $session->flash('warning', 'Academic terms cannot be archived because they preserve permanent student academic records. Use Term Closure to close and lock terms.');
        redirect('/admin/academic-terms/closure');
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

    public function updateSchoolYear(Request $request, Response $response, Session $session, string $id): void
    {
        $yearId = (int) $id;
        $schoolYear = trim((string) $request->input('school_year', ''));

        // Normalize input
        $schoolYear = str_replace(['–', '—', '/'], '-', $schoolYear);
        $schoolYear = preg_replace('/\s*-\s*/', '-', $schoolYear);
        if (preg_match('/^(\d{4})$/', $schoolYear, $singleMatch)) {
            $yStart = (int) $singleMatch[1];
            $schoolYear = $yStart . '-' . ($yStart + 1);
        }

        if (!preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)) {
            $session->flash('error', 'School year must use format YYYY-YYYY, for example 2026-2027.');
            redirect('/admin/academic-terms?tab=school-years');
            return;
        }

        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE academic_years SET school_year = :name, updated_at = NOW() WHERE id = :id")
            ->execute(['name' => $schoolYear, 'id' => $yearId]);
        $pdo->prepare("UPDATE academic_terms SET school_year = :name WHERE academic_year_id = :id")
            ->execute(['name' => $schoolYear, 'id' => $yearId]);

        $session->flash('success', "School year updated to {$schoolYear}.");
        redirect('/admin/academic-terms?tab=school-years');
    }

    public function toggleActiveYear(Request $request, Response $response, Session $session, string $id): void
    {
        $yearId = (int) $id;
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM academic_years WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $yearId]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$year) {
            $session->flash('error', 'School year not found.');
            redirect('/admin/academic-terms?tab=school-years');
            return;
        }

        // Prevent activating past ended school years
        $syName = $year['school_year'];
        $currentYearInt = (int) date('Y');
        $currentMonth = (int) date('n');
        if (preg_match('/^(\d{4})-(\d{4})$/', $syName, $m)) {
            $endY = (int) $m[2];
            if ($currentYearInt > $endY || ($currentYearInt === $endY && $currentMonth >= 6)) {
                $session->flash('error', "Cannot activate ended school year {$syName}. Past academic years are permanent records and cannot be reactivated.");
                redirect('/admin/academic-terms?tab=school-years');
                return;
            }
        }

        $pdo->exec("UPDATE academic_years SET is_active = 0");
        $pdo->prepare("UPDATE academic_years SET is_active = 1 WHERE id = :id")->execute(['id' => $yearId]);

        // Activate 1st semester of this year if exists, else first term of this year
        $stmtTerm = $pdo->prepare("SELECT id FROM academic_terms WHERE academic_year_id = :id ORDER BY semester ASC LIMIT 1");
        $stmtTerm->execute(['id' => $yearId]);
        $termId = $stmtTerm->fetchColumn();

        if ($termId) {
            $pdo->exec("UPDATE academic_terms SET is_active = 0");
            $pdo->prepare("UPDATE academic_terms SET is_active = 1 WHERE id = :id")->execute(['id' => $termId]);
        }

        $session->flash('success', "School year {$year['school_year']} set as current active.");
        redirect('/admin/academic-terms?tab=school-years');
    }
}
