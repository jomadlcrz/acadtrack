<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\AcademicTerm;

class SettingsController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $db = Database::getConnection();

        $termsStmt = $db->query("
            SELECT at.*, 
                   COALESCE(at.school_year, ay.school_year, '2026-2027') as school_year_display,
                   ay.school_year as academic_year_name
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.is_archived = 0
            ORDER BY COALESCE(at.school_year, ay.school_year) DESC, at.semester ASC
        ");
        $allTerms = $termsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $activeTerm = AcademicTerm::getActive();
        $selectedTermId = (int) $request->get('term_id', 0);

        $term = null;
        if ($selectedTermId > 0) {
            foreach ($allTerms as $t) {
                if ((int)$t['id'] === $selectedTermId) {
                    $term = $t;
                    break;
                }
            }
        }
        if (!$term) {
            $term = $activeTerm ?: ($allTerms[0] ?? null);
        }

        $settings = null;
        if ($term) {
            $stmt = $db->prepare("SELECT * FROM grading_settings WHERE academic_term_id = :term_id AND subject_id IS NULL LIMIT 1");
            $stmt->execute(['term_id' => $term['id']]);
            $settings = $stmt->fetch() ?: null;
        }

        // Fallback default weights if not yet configured
        if (!$settings) {
            $settings = [
                'grading_method' => 'zero_based',
                'min_grade' => 0.00,
                'max_grade' => 100.00,
                'prelim_weight' => 20.00,
                'midterm_weight' => 20.00,
                'semi_final_weight' => 20.00,
                'final_weight' => 40.00,
            ];
        }

        $html = (new \App\Core\View())->render('admin.settings.index', [
            'term' => $term,
            'allTerms' => $allTerms,
            'settings' => $settings,
        ]);
        $response->html($html);
    }

    public function update(Request $request, Response $response, Session $session): void
    {
        $db = Database::getConnection();

        $termId = (int) $request->post('academic_term_id', 0);
        $term = null;
        if ($termId > 0) {
            $stmt = $db->prepare("SELECT * FROM academic_terms WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $termId]);
            $term = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$term) {
            $term = AcademicTerm::getActive();
        }

        if (!$term) {
            $session->flash('error', 'Please select an academic term to configure.');
            redirect('/admin/settings');
            return;
        }

        $gradingMethod = $request->post('grading_method', 'zero_based');
        $academicYear = trim((string) $request->post('academic_year', ''));

        $prelimWeight = (float) $request->post('prelim_weight', 20.00);
        $midtermWeight = (float) $request->post('midterm_weight', 20.00);
        $semiFinalWeight = (float) $request->post('semi_final_weight', 20.00);
        $finalWeight = (float) $request->post('final_weight', 40.00);

        $totalWeight = round($prelimWeight + $midtermWeight + $semiFinalWeight + $finalWeight, 2);

        if (abs($totalWeight - 100.00) > 0.01) {
            $session->flash('error', "Period weights must sum to exactly 100%. Current total: {$totalWeight}%.");
            redirect('/admin/settings?term_id=' . $term['id']);
            return;
        }

        $minGrade = ($gradingMethod === 'fifty_based') ? 50.00 : 0.00;

        $stmt = $db->prepare("SELECT id FROM grading_settings WHERE academic_term_id = :term_id AND subject_id IS NULL LIMIT 1");
        $stmt->execute(['term_id' => $term['id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $updateStmt = $db->prepare("
                UPDATE grading_settings 
                SET grading_method = :method, 
                    min_grade = :min, 
                    max_grade = 100.00,
                    prelim_weight = :prelim, 
                    midterm_weight = :midterm, 
                    semi_final_weight = :semifinal, 
                    final_weight = :final,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                'method' => $gradingMethod,
                'min' => $minGrade,
                'prelim' => $prelimWeight,
                'midterm' => $midtermWeight,
                'semifinal' => $semiFinalWeight,
                'final' => $finalWeight,
                'id' => $existing['id'],
            ]);
        } else {
            $insertStmt = $db->prepare("
                INSERT INTO grading_settings 
                (academic_term_id, grading_method, min_grade, max_grade, prelim_weight, midterm_weight, semi_final_weight, final_weight, created_at, updated_at) 
                VALUES (:term_id, :method, :min, 100.00, :prelim, :midterm, :semifinal, :final, NOW(), NOW())
            ");
            $insertStmt->execute([
                'term_id' => $term['id'],
                'method' => $gradingMethod,
                'min' => $minGrade,
                'prelim' => $prelimWeight,
                'midterm' => $midtermWeight,
                'semifinal' => $semiFinalWeight,
                'final' => $finalWeight,
            ]);
        }

        // Sync individual grading_periods weights for the selected academic term
        $periods = [
            'Prelim' => $prelimWeight,
            'Midterm' => $midtermWeight,
            'Semi-Final' => $semiFinalWeight,
            'Final' => $finalWeight,
        ];
        foreach ($periods as $periodName => $weight) {
            $db->prepare("UPDATE grading_periods SET weight = :w WHERE academic_term_id = :term_id AND name = :name")
                ->execute(['w' => $weight, 'term_id' => $term['id'], 'name' => $periodName]);
        }

        // Activate this term as the primary active term
        $db->exec("UPDATE academic_terms SET is_active = 0");
        $db->exec("UPDATE academic_years SET is_active = 0");
        $db->prepare("UPDATE academic_terms SET is_active = 1, is_archived = 0 WHERE id = :id")->execute(['id' => $term['id']]);
        if (!empty($term['academic_year_id'])) {
            $db->prepare("UPDATE academic_years SET is_active = 1 WHERE id = :id")->execute(['id' => $term['academic_year_id']]);
        }

        if ($academicYear !== '' && !empty($term['academic_year_id'])) {
            $ayStmt = $db->prepare("UPDATE academic_years SET school_year = :name WHERE id = :id");
            $ayStmt->execute(['name' => $academicYear, 'id' => $term['academic_year_id']]);
        }

        $session->flash('success', 'Institutional grading policies and active academic term updated successfully.');
        redirect('/admin/settings?term_id=' . $term['id']);
    }
}
