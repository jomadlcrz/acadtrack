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
            $stmt = $db->prepare("SELECT * FROM grading_settings WHERE academic_term_id = :term_id LIMIT 1");
            $stmt->execute(['term_id' => $term['id']]);
            $settings = $stmt->fetch() ?: null;
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
        $gradingMethod = $request->post('grading_method', 'zero_based');
        $academicYear = trim((string) $request->post('academic_year', ''));

        $db = Database::getConnection();

        if ($term) {
            $minGrade = ($gradingMethod === 'fifty_based') ? 50.00 : 0.00;
            $stmt = $db->prepare("SELECT id FROM grading_settings WHERE academic_term_id = :term_id LIMIT 1");
            $stmt->execute(['term_id' => $term['id']]);
            $existing = $stmt->fetch();

            if ($existing) {
                $updateStmt = $db->prepare("UPDATE grading_settings SET grading_method = :method, min_grade = :min WHERE id = :id");
                $updateStmt->execute(['method' => $gradingMethod, 'min' => $minGrade, 'id' => $existing['id']]);
            } else {
                $insertStmt = $db->prepare("INSERT INTO grading_settings (academic_term_id, grading_method, min_grade, max_grade) VALUES (:term_id, :method, :min, 100.00)");
                $insertStmt->execute(['term_id' => $term['id'], 'method' => $gradingMethod, 'min' => $minGrade]);
            }

            if ($academicYear !== '' && !empty($term['academic_year_id'])) {
                $ayStmt = $db->prepare("UPDATE academic_years SET school_year = :name WHERE id = :id");
                $ayStmt->execute(['name' => $academicYear, 'id' => $term['academic_year_id']]);
            }
        }

        $session->flash('success', 'Settings updated successfully.');
        redirect('/admin/settings');
    }
}
