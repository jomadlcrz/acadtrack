<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\GradingPeriod;
use App\Models\Subject;
use PDO;

class RankingService
{
    /**
     * Compute comprehensive rankings, statistics, and grade distribution for a subject.
     *
     * @param int $subjectId
     * @param int $termId
     * @param int|null $setId
     * @param int $limit
     * @return array
     */
    public function getSubjectRankings(int $subjectId, int $termId, ?int $setId = null, int $limit = 10): array
    {
        $pdo = Database::getConnection();

        // 1. Fetch grading periods for this term
        $periods = GradingPeriod::where('academic_term_id', $termId)->orderBy('order_num')->get()->toArray();
        if (empty($periods)) {
            $periods = GradingPeriod::getActive();
        }

        // 2. Fetch enrolled students with student details & set name
        $setClause = $setId ? "AND (st.set_id = :set_id OR sd.set_id = :set_id)" : "";
        $query = "
            SELECT st.id as student_id, st.year_level, st.status as student_status,
                   sd.first_name, sd.last_name, sd.student_number,
                   COALESCE(s.set_name, 'Unassigned') as set_name,
                   st.set_id
            FROM enrollments e
            JOIN students st ON e.student_id = st.id
            JOIN users u ON st.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            LEFT JOIN sets s ON COALESCE(st.set_id, sd.set_id) = s.id
            WHERE e.subject_id = :subject_id
              AND e.academic_term_id = :term_id
              {$setClause}
            ORDER BY sd.last_name ASC, sd.first_name ASC
        ";

        $stmt = $pdo->prepare($query);
        $params = ['subject_id' => $subjectId, 'term_id' => $termId];
        if ($setId) {
            $params['set_id'] = $setId;
        }
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            $emptyDist = $this->getEmptyDistribution();
            return [
                'total_students' => 0,
                'passed_count' => 0,
                'failed_count' => 0,
                'incomplete_count' => 0,
                'class_average' => 0.0,
                'highest_grade' => 0.0,
                'lowest_grade' => 0.0,
                'passing_rate' => 0.0,
                'overall_top' => [],
                'top_performers' => [],
                'period_top' => [
                    'prelim' => [],
                    'midterm' => [],
                    'semi_final' => [],
                    'final' => [],
                ],
                'period_leaders' => [
                    'prelim' => [],
                    'midterm' => [],
                    'semi_final' => [],
                    'final' => [],
                ],
                'distribution' => $emptyDist,
                'students' => [],
                'periods' => $periods,
                'stats' => [
                    'count' => 0,
                    'average' => 0.0,
                    'passed' => 0,
                    'failed' => 0,
                    'incomplete' => 0,
                    'passing_rate' => 0.0,
                ],
            ];
        }

        // 3. Fetch all grades for these students in this subject
        $gradeStmt = $pdo->prepare("
            SELECT student_id, grading_period_id, grade
            FROM grades
            WHERE subject_id = :subject_id
              AND academic_term_id = :term_id
        ");
        $gradeStmt->execute(['subject_id' => $subjectId, 'term_id' => $termId]);
        $allGrades = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        $gradeMatrix = []; // [student_id => [period_id => grade]]
        foreach ($allGrades as $g) {
            $gradeMatrix[(int)$g['student_id']][(int)$g['grading_period_id']] = (float)$g['grade'];
        }

        // 4. Compute student final grades and period scores
        $totalWeights = 0;
        foreach ($periods as $p) {
            $totalWeights += (float) ($p['weight'] ?? 25.0);
        }
        if ($totalWeights <= 0) $totalWeights = 100.0;

        $studentList = [];
        $validFinalGrades = [];
        $passedCount = 0;
        $failedCount = 0;
        $incompleteCount = 0;

        foreach ($students as $s) {
            $sId = (int)$s['student_id'];
            $pGrades = $gradeMatrix[$sId] ?? [];

            $weightedSum = 0;
            $periodsCount = count($periods);
            $hasAllPeriods = true;
            $periodScores = [];

            foreach ($periods as $p) {
                $pId = (int)$p['id'];
                $pName = $p['name'];
                $weight = (float)($p['weight'] ?? 25.0);

                if (isset($pGrades[$pId])) {
                    $score = $pGrades[$pId];
                    $periodScores[$pName] = $score;
                    $weightedSum += $score * ($weight / $totalWeights);
                } else {
                    $hasAllPeriods = false;
                    $periodScores[$pName] = null;
                }
            }

            $finalGrade = $hasAllPeriods ? round($weightedSum, 2) : null;

            if ($finalGrade !== null) {
                $validFinalGrades[] = $finalGrade;
                if ($finalGrade >= 75.0) {
                    $passedCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $incompleteCount++;
            }

            $studentList[] = [
                'student_id' => $sId,
                'name' => trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                'full_name' => trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                'student_number' => $s['student_number'] ?? '',
                'set_name' => $s['set_name'] ?? 'Unassigned',
                'year_level' => (int)($s['year_level'] ?? 1),
                'status' => $s['student_status'] ?? 'Regular',
                'period_scores' => $periodScores,
                'final_grade' => $finalGrade,
                'is_passed' => $finalGrade !== null && $finalGrade >= 75.0,
                'is_incomplete' => $finalGrade === null,
            ];
        }

        // 5. Aggregate statistics
        $totalStudents = count($studentList);
        $classAverage = !empty($validFinalGrades) ? round(array_sum($validFinalGrades) / count($validFinalGrades), 2) : 0.0;
        $highestGrade = !empty($validFinalGrades) ? max($validFinalGrades) : 0.0;
        $lowestGrade = !empty($validFinalGrades) ? min($validFinalGrades) : 0.0;
        $passingRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 1) : 0.0;

        // 6. Overall Top Performers (by final_grade DESC)
        $ranked = array_filter($studentList, fn($s) => $s['final_grade'] !== null);
        usort($ranked, fn($a, $b) => $b['final_grade'] <=> $a['final_grade']);

        $overallTop = [];
        $currentRank = 1;
        $prevScore = null;
        foreach (array_slice($ranked, 0, $limit) as $idx => $st) {
            if ($prevScore !== null && $st['final_grade'] < $prevScore) {
                $currentRank = $idx + 1;
            }
            $prevScore = $st['final_grade'];
            $st['rank'] = $currentRank;
            $overallTop[] = $st;
        }

        // 7. Period-by-Period Top Performers
        $periodTop = [];
        foreach ($periods as $p) {
            $pName = $p['name'];
            $pList = [];
            foreach ($studentList as $st) {
                if ($st['period_scores'][$pName] !== null) {
                    $pList[] = [
                        'student_id' => $st['student_id'],
                        'name' => $st['name'],
                        'full_name' => $st['name'],
                        'student_number' => $st['student_number'],
                        'set_name' => $st['set_name'],
                        'score' => $st['period_scores'][$pName],
                    ];
                }
            }
            usort($pList, fn($a, $b) => $b['score'] <=> $a['score']);

            $pTop = [];
            $prank = 1;
            $pPrev = null;
            foreach (array_slice($pList, 0, $limit) as $idx => $item) {
                if ($pPrev !== null && $item['score'] < $pPrev) {
                    $prank = $idx + 1;
                }
                $pPrev = $item['score'];
                $item['rank'] = $prank;
                $pTop[] = $item;
            }
            $slug = strtolower(str_replace(['-', ' '], ['_', '_'], $pName));
            $periodTop[$pName] = $pTop;
            $periodTop[$slug] = $pTop;
        }

        // 8. Grade Distribution Histogram (0-49, 50-59, 60-69, 70-79, 80-89, 90-100)
        $distribution = [
            '0-49'   => ['label' => '0–49', 'min' => 0.0, 'max' => 49.99, 'count' => 0, 'color' => '#dc3545', 'percentage' => 0.0],
            '50-59'  => ['label' => '50–59', 'min' => 50.0, 'max' => 59.99, 'count' => 0, 'color' => '#fd7e14', 'percentage' => 0.0],
            '60-69'  => ['label' => '60–69', 'min' => 60.0, 'max' => 69.99, 'count' => 0, 'color' => '#ffc107', 'percentage' => 0.0],
            '70-79'  => ['label' => '70–79', 'min' => 70.0, 'max' => 79.99, 'count' => 0, 'color' => '#0d6efd', 'percentage' => 0.0],
            '80-89'  => ['label' => '80–89', 'min' => 80.0, 'max' => 89.99, 'count' => 0, 'color' => '#20c997', 'percentage' => 0.0],
            '90-100' => ['label' => '90–100', 'min' => 90.0, 'max' => 100.0, 'count' => 0, 'color' => '#198754', 'percentage' => 0.0],
        ];

        foreach ($validFinalGrades as $score) {
            foreach ($distribution as &$bracket) {
                if ($score >= $bracket['min'] && $score <= $bracket['max']) {
                    $bracket['count']++;
                    break;
                }
            }
        }
        unset($bracket);

        $maxCount = max(array_column($distribution, 'count') ?: [1]);
        if ($maxCount <= 0) $maxCount = 1;
        foreach ($distribution as &$bracket) {
            $bracket['percentage'] = round(($bracket['count'] / $maxCount) * 100, 1);
        }
        unset($bracket);

        return [
            'total_students' => $totalStudents,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'incomplete_count' => $incompleteCount,
            'class_average' => $classAverage,
            'highest_grade' => $highestGrade,
            'lowest_grade' => $lowestGrade,
            'passing_rate' => $passingRate,
            'overall_top' => $overallTop,
            'top_performers' => $overallTop,
            'period_top' => $periodTop,
            'period_leaders' => $periodTop,
            'distribution' => $distribution,
            'students' => $studentList,
            'periods' => $periods,
            'stats' => [
                'count' => $totalStudents,
                'average' => $classAverage,
                'passed' => $passedCount,
                'failed' => $failedCount,
                'incomplete' => $incompleteCount,
                'passing_rate' => $passingRate,
            ],
        ];
    }

    /**
     * Dean's List / Top GWA Performers for an Academic Term.
     */
    public function getTermTopPerformers(int $termId, ?int $departmentId = null, int $limit = 10): array
    {
        $pdo = Database::getConnection();

        $deptClause = $departmentId ? "AND p.department_id = :dept_id" : "";

        $stmt = $pdo->prepare("
            SELECT st.id as student_id, sd.first_name, sd.last_name, sd.student_number,
                   p.program_abbrev, s.set_name,
                   ROUND(AVG(g.grade), 2) as gwa,
                   COUNT(DISTINCT g.subject_id) as subjects_count
            FROM students st
            JOIN users u ON st.user_id = u.id
            JOIN student_details sd ON sd.user_id = u.id
            LEFT JOIN programs p ON sd.program_id = p.id
            LEFT JOIN sets s ON sd.set_id = s.id
            JOIN grades g ON g.student_id = st.id AND g.academic_term_id = :term_id
            WHERE 1=1 {$deptClause}
            GROUP BY st.id, sd.first_name, sd.last_name, sd.student_number, p.program_abbrev, s.set_name
            HAVING gwa >= 75.0
            ORDER BY gwa DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':term_id', $termId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($departmentId) {
            $stmt->bindValue(':dept_id', $departmentId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rank = 1;
        $prevGwa = null;
        foreach ($rows as $idx => &$row) {
            $gwa = (float)$row['gwa'];
            if ($prevGwa !== null && $gwa < $prevGwa) {
                $rank = $idx + 1;
            }
            $prevGwa = $gwa;
            $row['rank'] = $rank;
            $row['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
            $row['full_name'] = $row['name'];
            $row['subject_count'] = (int)($row['subjects_count'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    private function getEmptyDistribution(): array
    {
        return [
            '0-49'   => ['label' => '0–49', 'min' => 0.0, 'max' => 49.99, 'count' => 0, 'color' => '#dc3545', 'percentage' => 0.0],
            '50-59'  => ['label' => '50–59', 'min' => 50.0, 'max' => 59.99, 'count' => 0, 'color' => '#fd7e14', 'percentage' => 0.0],
            '60-69'  => ['label' => '60–69', 'min' => 60.0, 'max' => 69.99, 'count' => 0, 'color' => '#ffc107', 'percentage' => 0.0],
            '70-79'  => ['label' => '70–79', 'min' => 70.0, 'max' => 79.99, 'count' => 0, 'color' => '#0d6efd', 'percentage' => 0.0],
            '80-89'  => ['label' => '80–89', 'min' => 80.0, 'max' => 89.99, 'count' => 0, 'color' => '#20c997', 'percentage' => 0.0],
            '90-100' => ['label' => '90–100', 'min' => 90.0, 'max' => 100.0, 'count' => 0, 'color' => '#198754', 'percentage' => 0.0],
        ];
    }
}
