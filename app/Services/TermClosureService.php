<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Models\GradingSheet;
use PDO;

class TermClosureService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Get all terms with closure states, periods, and stats.
     */
    public function getClosures(): array
    {
        $stmt = $this->pdo->query("
            SELECT at.*,
                   COALESCE(at.school_year, ay.school_year, '2026-2027') as school_year_display,
                   ay.school_year as academic_year_name,
                   u.email as closed_by_email,
                   CASE 
                       WHEN ad.first_name IS NOT NULL THEN CONCAT(ad.first_name, ' ', ad.last_name)
                       WHEN fd.first_name IS NOT NULL THEN CONCAT(fd.first_name, ' ', fd.last_name)
                       ELSE u.email 
                   END as closed_by_name
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            LEFT JOIN users u ON at.closed_by = u.id
            LEFT JOIN admin_details ad ON u.id = ad.user_id
            LEFT JOIN faculty_details fd ON u.id = fd.user_id
            ORDER BY COALESCE(at.school_year, ay.school_year) DESC, at.semester ASC
        ");
        $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($terms as $term) {
            $termId = (int) $term['id'];
            $semNum = (int) $term['semester'];
            $semName = match ($semNum) {
                1 => '1st Semester',
                2 => '2nd Semester',
                3 => 'Summer',
                default => "Semester {$semNum}",
            };

            // Grading periods
            $stmtGp = $this->pdo->prepare("
                SELECT * FROM grading_periods 
                WHERE academic_term_id = :term_id 
                ORDER BY order_num ASC
            ");
            $stmtGp->execute(['term_id' => $termId]);
            $periods = $stmtGp->fetchAll(PDO::FETCH_ASSOC);

            // Stats
            $stmtStats = $this->pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM subjects WHERE academic_term_id = :t1 AND (is_archived = 0 OR is_archived IS NULL)) as subjects_count,
                    (SELECT COUNT(*) FROM sets WHERE academic_term_id = :t2 AND status = 'active') as sets_count,
                    (SELECT COUNT(DISTINCT s.id) FROM students s JOIN sets st ON s.set_id = st.id WHERE st.academic_term_id = :t3) as students_count,
                    (SELECT COUNT(*) FROM grades WHERE academic_term_id = :t4) as grades_count,
                    (SELECT COUNT(*) FROM grading_sheets WHERE academic_term_id = :t5) as sheets_count,
                    (SELECT COUNT(*) FROM grading_sheets WHERE academic_term_id = :t6 AND status IN ('APPROVED', 'FINALIZED')) as approved_sheets_count,
                    (SELECT COUNT(*) FROM grading_sheets WHERE academic_term_id = :t7 AND status NOT IN ('APPROVED', 'FINALIZED')) as pending_sheets_count
            ");
            $stmtStats->execute([
                't1' => $termId, 't2' => $termId, 't3' => $termId, 
                't4' => $termId, 't5' => $termId, 't6' => $termId, 't7' => $termId
            ]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [];

            $isClosed = (bool) ($term['is_closed'] ?? false);
            $isActive = (bool) ($term['is_active'] ?? false);
            $isArchived = (bool) ($term['is_archived'] ?? false);

            $currentYearInt = (int) date('Y');
            $currentMonth = (int) date('n');
            $isEnded = false;
            $syDisplay = $term['school_year_display'] ?? '';
            if (preg_match('/^(\d{4})-(\d{4})$/', $syDisplay, $m)) {
                $endY = (int) $m[2];
                if ($currentYearInt > $endY || ($currentYearInt === $endY && $currentMonth >= 6)) {
                    $isEnded = true;
                }
            }

            $reopenable = !$isEnded;

            $statusKey = 'open';
            $statusLabel = 'Open';
            $statusBadge = 'bg-secondary';

            if ($isEnded) {
                $statusKey = 'ended';
                $statusLabel = 'Ended';
                $statusBadge = 'bg-secondary-subtle text-secondary';
            } elseif ($isClosed) {
                $statusKey = 'closed';
                $statusLabel = 'Closed';
                $statusBadge = 'bg-danger text-white';
            } elseif ($isActive) {
                $statusKey = 'active';
                $statusLabel = 'Active';
                $statusBadge = 'bg-success text-white';
            } elseif ($isArchived) {
                $statusKey = 'archived';
                $statusLabel = 'Archived';
                $statusBadge = 'bg-dark text-white';
            }

            $results[] = [
                'id' => $termId,
                'academic_year_id' => (int) $term['academic_year_id'],
                'school_year' => $syDisplay,
                'semester' => $semNum,
                'semester_name' => $semName,
                'is_active' => $isActive,
                'is_closed' => $isClosed,
                'is_archived' => $isArchived,
                'is_ended' => $isEnded,
                'reopenable' => $reopenable,
                'closed_at' => $term['closed_at'],
                'closed_by' => $term['closed_by'],
                'closed_by_name' => $term['closed_by_name'],
                'closure_reason' => $term['closure_reason'],
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'periods' => $periods,
                'stats' => $stats,
            ];
        }

        return $results;
    }

    /**
     * Get detailed pre-closure verification preview for a specific term.
     */
    public function getTermPreview(int $termId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT at.*,
                   COALESCE(at.school_year, ay.school_year, '2026-2027') as school_year_display,
                   ay.school_year as academic_year_name,
                   COALESCE(CONCAT(ad.first_name, ' ', ad.last_name), u.email, 'Registrar') as closed_by_name
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            LEFT JOIN users u ON at.closed_by = u.id
            LEFT JOIN admin_details ad ON u.id = ad.user_id
            WHERE at.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $termId]);
        $term = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$term) {
            return null;
        }

        $semNum = (int) $term['semester'];
        $semName = match ($semNum) {
            1 => '1st Semester',
            2 => '2nd Semester',
            3 => 'Summer',
            default => "Semester {$semNum}",
        };

        // Grading periods
        $stmtGp = $this->pdo->prepare("SELECT * FROM grading_periods WHERE academic_term_id = :term_id ORDER BY order_num ASC");
        $stmtGp->execute(['term_id' => $termId]);
        $periods = $stmtGp->fetchAll(PDO::FETCH_ASSOC);

        // Subjects count
        $stmtSubj = $this->pdo->prepare("
            SELECT COUNT(*) FROM subjects 
            WHERE academic_term_id = :id AND (is_archived = 0 OR is_archived IS NULL)
        ");
        $stmtSubj->execute(['id' => $termId]);
        $subjectsCount = (int) $stmtSubj->fetchColumn();

        // Enrolled students count
        $stmtStud = $this->pdo->prepare("
            SELECT COUNT(DISTINCT s.id) 
            FROM students s 
            JOIN sets st ON s.set_id = st.id 
            WHERE st.academic_term_id = :id
        ");
        $stmtStud->execute(['id' => $termId]);
        $studentsCount = (int) $stmtStud->fetchColumn();

        // Grading sheets audit
        $stmtSheets = $this->pdo->prepare("
            SELECT gs.id, gs.status, gs.grading_period_id, gp.name as period_name,
                   s.subject_code, s.descriptive_title,
                   COALESCE(CONCAT(fd.first_name, ' ', fd.last_name), u.email) as faculty_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            LEFT JOIN users u ON gs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON u.id = fd.user_id
            WHERE gs.academic_term_id = :id
            ORDER BY gs.status ASC, s.subject_code ASC
        ");
        $stmtSheets->execute(['id' => $termId]);
        $sheets = $stmtSheets->fetchAll(PDO::FETCH_ASSOC);

        $completedSheets = [];
        $pendingSheets = [];

        foreach ($sheets as $s) {
            if (in_array($s['status'], ['APPROVED', 'FINALIZED'], true)) {
                $completedSheets[] = $s;
            } else {
                $pendingSheets[] = $s;
            }
        }

        // Check next term for workflow recommendation
        $stmtNext = $this->pdo->prepare("
            SELECT id, semester FROM academic_terms 
            WHERE academic_year_id = :yid AND semester > :sem 
            ORDER BY semester ASC LIMIT 1
        ");
        $stmtNext->execute(['yid' => $term['academic_year_id'], 'sem' => $semNum]);
        $nextTerm = $stmtNext->fetch(PDO::FETCH_ASSOC);

        $effects = [
            [
                'title' => 'Grade Sheets & Evaluation Lock',
                'description' => 'All 4 grading periods will be closed. Faculty score encoding and submissions will be frozen.',
                'icon' => 'bi-lock',
                'variant' => 'danger'
            ],
            [
                'title' => 'Attendance Records Lock',
                'description' => 'Daily attendance records and absence tallies will become permanent and read-only.',
                'icon' => 'bi-calendar-check',
                'variant' => 'warning'
            ],
            [
                'title' => 'Transcript & Academic Integrity',
                'description' => 'Student enrollment, subject assignments, and grade points are preserved as official historical data.',
                'icon' => 'bi-shield-check',
                'variant' => 'info'
            ],
        ];

        return [
            'term' => [
                'id' => $termId,
                'school_year' => $term['school_year_display'],
                'semester' => $semNum,
                'semester_name' => $semName,
                'is_active' => (bool) $term['is_active'],
                'is_closed' => (bool) $term['is_closed'],
                'closed_at' => $term['closed_at'],
                'closed_by_name' => $term['closed_by_name'] ?? 'Registrar',
                'closure_reason' => $term['closure_reason'],
            ],
            'periods' => $periods,
            'counts' => [
                'subjects' => $subjectsCount,
                'students' => $studentsCount,
                'total_sheets' => count($sheets),
                'completed_sheets' => count($completedSheets),
                'pending_sheets' => count($pendingSheets),
            ],
            'pending_sheets' => $pendingSheets,
            'next_term' => $nextTerm,
            'effects' => $effects,
            'default_reason' => "{$semName} officially closed and posted by Registrar/Dean",
        ];
    }

    /**
     * Officially close and lock an academic term.
     */
    public function closeTerm(int $termId, int $userId, ?string $reason, bool $activateNext = false): array
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Lock the academic term
            $stmt = $this->pdo->prepare("
                UPDATE academic_terms 
                SET is_closed = 1,
                    closed_at = NOW(),
                    closed_by = :user_id,
                    closure_reason = :reason,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'user_id' => $userId,
                'reason' => !empty($reason) ? trim($reason) : 'Term officially closed',
                'id' => $termId,
            ]);

            // 2. Lock all grading periods for this term
            $stmtPeriods = $this->pdo->prepare("
                UPDATE grading_periods
                SET is_closed = 1,
                    closed_at = NOW(),
                    closure_reason = 'Closed with academic term',
                    updated_at = NOW()
                WHERE academic_term_id = :term_id
            ");
            $stmtPeriods->execute(['term_id' => $termId]);

            // 3. Optional rollover: activate next semester if requested
            $nextTermActivated = null;
            if ($activateNext) {
                // Get current term info
                $curr = $this->pdo->prepare("SELECT academic_year_id, semester FROM academic_terms WHERE id = :id");
                $curr->execute(['id' => $termId]);
                $termData = $curr->fetch(PDO::FETCH_ASSOC);

                if ($termData) {
                    $stmtNext = $this->pdo->prepare("
                        SELECT id, semester FROM academic_terms 
                        WHERE academic_year_id = :yid AND semester > :sem AND is_archived = 0
                        ORDER BY semester ASC LIMIT 1
                    ");
                    $stmtNext->execute(['yid' => $termData['academic_year_id'], 'sem' => $termData['semester']]);
                    $nextRow = $stmtNext->fetch(PDO::FETCH_ASSOC);

                    if ($nextRow) {
                        $this->pdo->exec("UPDATE academic_terms SET is_active = 0");
                        $this->pdo->prepare("UPDATE academic_terms SET is_active = 1, is_closed = 0 WHERE id = :id")
                            ->execute(['id' => $nextRow['id']]);
                        $nextTermActivated = (int) $nextRow['id'];
                    }
                }
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Academic term officially closed and locked.',
                'next_term_id' => $nextTermActivated,
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Reopen an academic term with an audited administrative reason.
     */
    public function reopenTerm(int $termId, int $userId, string $reason): array
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('A specific reason is required to reopen an official academic term.');
        }

        // Refuse reopening if school year has ended
        $stmtTerm = $this->pdo->prepare("
            SELECT at.*, COALESCE(at.school_year, ay.school_year, '') as school_year_display
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.id = :id
        ");
        $stmtTerm->execute(['id' => $termId]);
        $termData = $stmtTerm->fetch(PDO::FETCH_ASSOC);

        if ($termData) {
            $syDisplay = $termData['school_year_display'];
            $currentYearInt = (int) date('Y');
            $currentMonth = (int) date('n');
            if (preg_match('/^(\d{4})-(\d{4})$/', $syDisplay, $m)) {
                $endY = (int) $m[2];
                if ($currentYearInt > $endY || ($currentYearInt === $endY && $currentMonth >= 6)) {
                    throw new \DomainException("School year {$syDisplay} has ended. Historical academic records are permanently sealed and cannot be reopened.");
                }
            }
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE academic_terms 
                SET is_closed = 0,
                    closed_at = NULL,
                    closed_by = NULL,
                    closure_reason = NULL,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute(['id' => $termId]);

            // Reopen grading periods
            $stmtPeriods = $this->pdo->prepare("
                UPDATE grading_periods
                SET is_closed = 0,
                    closed_at = NULL,
                    closure_reason = NULL,
                    updated_at = NOW()
                WHERE academic_term_id = :term_id
            ");
            $stmtPeriods->execute(['term_id' => $termId]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Academic term reopened for corrections.',
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Lock or unlock an individual grading period (Prelim, Midterm, etc.).
     */
    public function togglePeriodState(int $periodId, string $status, ?string $reason = null): array
    {
        $isClosed = ($status === 'closed') ? 1 : 0;
        $closedAt = $isClosed ? date('Y-m-d H:i:s') : null;
        $closureReason = $isClosed ? (!empty($reason) ? trim($reason) : 'Grading period locked') : null;

        $stmt = $this->pdo->prepare("
            UPDATE grading_periods
            SET is_closed = :is_closed,
                closed_at = :closed_at,
                closure_reason = :closure_reason,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'is_closed' => $isClosed,
            'closed_at' => $closedAt,
            'closure_reason' => $closureReason,
            'id' => $periodId,
        ]);

        return [
            'success' => true,
            'message' => $isClosed ? 'Grading period locked.' : 'Grading period opened.',
        ];
    }

    /**
     * Determine whether a school year has ended based on calendar cutoffs.
     */
    public function isSchoolYearEnded(string $schoolYear): bool
    {
        $currentYearInt = (int) date('Y');
        $currentMonth = (int) date('n');

        if (preg_match('/^(\d{4})-(\d{4})$/', trim($schoolYear), $m)) {
            $endY = (int) $m[2];
            return ($currentYearInt > $endY || ($currentYearInt === $endY && $currentMonth >= 6));
        }

        return false;
    }

    /**
     * Determine whether an academic term is closed or belongs to an ended school year.
     */
    public function isTermClosed(int $termId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT at.is_closed, at.school_year, ay.school_year as ay_school_year
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $termId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        if (!empty($row['is_closed'])) {
            return true;
        }

        $syName = !empty($row['school_year']) ? $row['school_year'] : ($row['ay_school_year'] ?? '');
        return $this->isSchoolYearEnded((string) $syName);
    }
}
