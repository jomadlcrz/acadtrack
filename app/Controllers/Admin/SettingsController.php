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
        $term = AcademicTerm::getActive();
        $db = Database::getConnection();
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
            'settings' => $settings,
        ]);
        $response->html($html);
    }

    public function update(Request $request, Response $response, Session $session): void
    {
        $term = AcademicTerm::getActive();
        if (!$term) {
            $session->flash('error', 'No active academic term found to configure.');
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
            redirect('/admin/settings');
            return;
        }

        $db = Database::getConnection();
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

        // Sync individual grading_periods weights for the active academic term
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

        if ($academicYear !== '' && !empty($term['academic_year_id'])) {
            $ayStmt = $db->prepare("UPDATE academic_years SET school_year = :name WHERE id = :id");
            $ayStmt->execute(['name' => $academicYear, 'id' => $term['academic_year_id']]);
        }

        $session->flash('success', 'Institutional grading policies and evaluation weights updated successfully.');
        redirect('/admin/settings');
    }
}
