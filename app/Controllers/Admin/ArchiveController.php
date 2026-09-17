<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Database;
use App\Models\Subject;
use App\Models\Set;
use App\Models\Department;
use App\Models\User;
use PDO;

class ArchiveController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $activeTab = trim((string) $request->get('tab', 'subjects'));
        if (!in_array($activeTab, ['subjects', 'sets', 'terms', 'departments', 'users'], true)) {
            $activeTab = 'subjects';
        }

        $pdo = Database::getConnection();

        // Fetch counts
        $countSubjects = Subject::where('is_archived', 1)->count();
        $countSets = Set::where('status', 'inactive')->count();
        
        $stmtTermCount = $pdo->query("SELECT COUNT(*) FROM academic_terms WHERE is_archived = 1");
        $countTerms = (int) $stmtTermCount->fetchColumn();

        $countDepartments = Department::where('status', 'inactive')->count();
        $countUsers = User::where('status', 'inactive')->count();
        $totalArchived = $countSubjects + $countSets + $countTerms + $countDepartments + $countUsers;

        // Fetch records for all tabs
        $archivedSubjects = Subject::where('is_archived', 1)
            ->with('program')
            ->orderBy('archived_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $archivedSets = Set::where('status', 'inactive')
            ->with(['program', 'academicTerm'])
            ->withCount('students')
            ->orderBy('updated_at', 'desc')
            ->get();

        $stmtTerms = $pdo->query("
            SELECT at.*, COALESCE(at.school_year, ay.school_year, 'N/A') as academic_year_name
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.is_archived = 1
            ORDER BY at.archived_at DESC, at.updated_at DESC
        ");
        $archivedTerms = $stmtTerms->fetchAll(PDO::FETCH_ASSOC);

        $archivedDepartments = Department::where('status', 'inactive')
            ->withCount('faculty')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

        $stmtUsers = $pdo->query("
            SELECT u.id, u.email, u.status, u.is_temp_password, u.created_at, u.deactivated_at,
                   COALESCE(ad.first_name, fd.first_name, sd.first_name, '') AS first_name,
                   COALESCE(ad.last_name, fd.last_name, sd.last_name, '') AS last_name,
                   COALESCE(r.role_name, 'Student') AS role,
                   sd.student_number,
                   fd.faculty_type,
                   d.dept_name AS department_name, d.dept_abbrev AS department_code, sec.set_name AS set_name
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            LEFT JOIN admin_details ad ON ad.user_id = u.id
            LEFT JOIN faculty_details fd ON fd.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            LEFT JOIN departments d ON d.id = fd.department_id
            LEFT JOIN sets sec ON sec.id = sd.set_id
            WHERE u.status = 'inactive'
            ORDER BY u.deactivated_at DESC, u.created_at DESC
        ");
        $archivedUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $html = (new View())->render('admin.archives.index', [
            'activeTab' => $activeTab,
            'countSubjects' => $countSubjects,
            'countSets' => $countSets,
            'countTerms' => $countTerms,
            'countDepartments' => $countDepartments,
            'countUsers' => $countUsers,
            'totalArchived' => $totalArchived,
            'archivedSubjects' => $archivedSubjects,
            'archivedSets' => $archivedSets,
            'archivedTerms' => $archivedTerms,
            'archivedDepartments' => $archivedDepartments,
            'archivedUsers' => $archivedUsers,
        ]);

        $response->html($html);
    }

    public function restoreSubject(Request $request, Response $response, Session $session, string $id): void
    {
        $subject = Subject::find((int) $id);
        if (!$subject) {
            $session->flash('error', 'Subject not found.');
            redirect('/admin/archives?tab=subjects');
            return;
        }

        $code = $subject->subject_code ?: $subject->code;
        $subject->update([
            'is_archived' => 0,
            'archived_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $session->flash('success', "Subject '{$code}' restored to active curriculum successfully.");
        redirect('/admin/archives?tab=subjects');
    }

    public function restoreSet(Request $request, Response $response, Session $session, string $id): void
    {
        $set = Set::find((int) $id);
        if (!$set) {
            $session->flash('error', 'Section not found.');
            redirect('/admin/archives?tab=sets');
            return;
        }

        $set->update([
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $session->flash('success', "Section '{$set->name}' restored to active status.");
        redirect('/admin/archives?tab=sets');
    }

    public function restoreAcademicTerm(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $pdo = Database::getConnection();
        $pdo->prepare("
            UPDATE academic_terms 
            SET is_archived = 0, archived_at = NULL, updated_at = NOW() 
            WHERE id = :id
        ")->execute(['id' => $termId]);

        $session->flash('success', 'Academic term restored to active status.');
        redirect('/admin/archives?tab=terms');
    }

    public function restoreDepartment(Request $request, Response $response, Session $session, string $id): void
    {
        $dept = Department::find((int) $id);
        if (!$dept) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/archives?tab=departments');
            return;
        }

        $dept->update([
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $session->flash('success', "Department '{$dept->name}' restored to active status.");
        redirect('/admin/archives?tab=departments');
    }

    public function restoreUser(Request $request, Response $response, Session $session, string $id): void
    {
        $userId = (int) $id;
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id, u.email, 
                   COALESCE(ad.first_name, fd.first_name, sd.first_name, '') AS first_name,
                   COALESCE(ad.last_name, fd.last_name, sd.last_name, '') AS last_name
            FROM users u
            LEFT JOIN admin_details ad ON ad.user_id = u.id
            LEFT JOIN faculty_details fd ON fd.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $session->flash('error', 'User not found.');
            redirect('/admin/archives?tab=users');
            return;
        }

        $pdo->prepare("
            UPDATE users 
            SET status = 'active', deactivated_at = NULL 
            WHERE id = :id
        ")->execute(['id' => $userId]);

        $fullName = trim($user['first_name'] . ' ' . $user['last_name']) ?: $user['email'];
        $session->flash('success', "User '{$fullName}' activated successfully.");
        redirect('/admin/archives?tab=users');
    }
}
