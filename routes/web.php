<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\DepartmentController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\AcademicTermController;
use App\Controllers\Admin\ProgramCurriculumController;
use App\Controllers\Admin\SetController as AdminSetController;
use App\Controllers\Admin\ArchiveController;
use App\Controllers\Dean\FacultyAssignmentController;
use App\Controllers\Dean\SubjectController as DeanSubjectController;
use App\Controllers\Dean\SetController;
use App\Controllers\Dean\GradeReviewController;
use App\Controllers\Faculty\SubjectController as FacultySubjectController;
use App\Controllers\Faculty\StudentController;
use App\Controllers\Faculty\GradingController;
use App\Controllers\Faculty\GradeSubmissionController;
use App\Controllers\Faculty\AttendanceController;
use App\Controllers\PublicVerificationController;
use App\Controllers\Student\GradeController;
use App\Controllers\Student\EvaluationController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\CsrfMiddleware;

// Home / Landing
$router->get('/', [HomeController::class, 'index']);

// Auth routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [new CsrfMiddleware()]);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [new CsrfMiddleware()]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [new CsrfMiddleware()]);
$router->get('/change-password', [AuthController::class, 'showChangePassword'], [new AuthMiddleware()]);
$router->post('/change-password', [AuthController::class, 'changePassword'], [new AuthMiddleware(), new CsrfMiddleware()]);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware()]);

// Admin routes
$router->get('/admin/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->get('/admin/users', [UserController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->get('/admin/users/create', [UserController::class, 'create'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/users', [UserController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/users/{id}', [UserController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/users/{id}/deactivate', [UserController::class, 'deactivate'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/users/{id}/activate', [UserController::class, 'activate'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->get('/admin/departments', [DepartmentController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/departments', [DepartmentController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/departments/{id}', [DepartmentController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/departments/{id}/archive', [DepartmentController::class, 'archive'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/departments/{id}/restore', [DepartmentController::class, 'restore'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

// Academic Terms routes
$router->get('/admin/academic-terms', [AcademicTermController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/academic-terms', [AcademicTermController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/academic-terms/{id}/toggle-active', [AcademicTermController::class, 'toggleActive'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/academic-terms/{id}/archive', [AcademicTermController::class, 'archive'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/academic-terms/{id}/restore', [AcademicTermController::class, 'restore'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

// Program Curricula routes (Admin only)
$router->get('/admin/program-curricula', [ProgramCurriculumController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->get('/admin/program-curricula/new', [ProgramCurriculumController::class, 'create'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->get('/admin/program-curricula/template-csv', [ProgramCurriculumController::class, 'downloadTemplateCsv'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/program-curricula', [ProgramCurriculumController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->get('/admin/program-curricula/{id}/export-csv', [ProgramCurriculumController::class, 'exportCsv'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/program-curricula/subjects', [ProgramCurriculumController::class, 'storeSubject'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/program-curricula/subjects/{id}', [ProgramCurriculumController::class, 'updateSubject'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/program-curricula/subjects/{id}/archive', [ProgramCurriculumController::class, 'archiveSubject'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);


// Sets routes
$router->get('/admin/sets', [AdminSetController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin', 'Dean'])]);
$router->post('/admin/sets', [AdminSetController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/sets/bulk-archive', [AdminSetController::class, 'bulkArchive'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/sets/{id}', [AdminSetController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/sets/{id}/archive', [AdminSetController::class, 'archive'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/sets/{id}/restore', [AdminSetController::class, 'restore'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

$router->get('/admin/settings', [SettingsController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/settings', [SettingsController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

// Centralized Archives Hub (Admin only)
$router->get('/admin/archives', [ArchiveController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/archives/subjects/{id}/restore', [ArchiveController::class, 'restoreSubject'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/archives/sets/{id}/restore', [ArchiveController::class, 'restoreSet'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/archives/academic-terms/{id}/restore', [ArchiveController::class, 'restoreAcademicTerm'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/archives/departments/{id}/restore', [ArchiveController::class, 'restoreDepartment'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/archives/users/{id}/restore', [ArchiveController::class, 'restoreUser'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

// Dean routes
$router->get('/dean/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->get('/dean/faculty-assignments', [FacultyAssignmentController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/faculty-assignments/assign', [FacultyAssignmentController::class, 'assign'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/faculty-assignments/remove', [FacultyAssignmentController::class, 'remove'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->get('/dean/subjects', [DeanSubjectController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->get('/dean/sets', [SetController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/sets', [SetController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/sets/{id}', [SetController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/sets/{id}/archive', [SetController::class, 'archive'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/sets/{id}/restore', [SetController::class, 'restore'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->get('/dean/grade-review', [GradeReviewController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->get('/dean/grade-review/{id}', [GradeReviewController::class, 'show'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/grade-review/approve', [GradeReviewController::class, 'approve'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/grade-review/confirm', [GradeReviewController::class, 'confirm'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/grade-review/return', [GradeReviewController::class, 'returnToFaculty'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/grade-review/{id}/edit', [GradeReviewController::class, 'updateGrade'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->get('/dean/grade-review/{id}/print', [GradeReviewController::class, 'printSheet'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);

// Faculty routes
$router->get('/faculty/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->get('/faculty/subjects', [FacultySubjectController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->post('/faculty/subjects/{id}/setup', [FacultySubjectController::class, 'setup'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->get('/faculty/students', [StudentController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->post('/faculty/students/add', [StudentController::class, 'addStudent'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->post('/faculty/students/enroll', [StudentController::class, 'enrollExisting'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->post('/faculty/students/remove', [StudentController::class, 'remove'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->get('/faculty/students/pass-data', [StudentController::class, 'getPassData'], [new AuthMiddleware(), new RoleMiddleware(['Faculty', 'Dean', 'Admin'])]);
$router->get('/faculty/grading', [GradingController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->post('/faculty/grading/save', [GradingController::class, 'save'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->post('/faculty/grading/submit', [GradingController::class, 'submit'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->get('/faculty/attendance', [AttendanceController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->post('/faculty/attendance/save', [AttendanceController::class, 'save'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->get('/faculty/attendance/student-history', [AttendanceController::class, 'getStudentHistory'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);

// Student routes
$router->get('/student/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);
$router->get('/student/grades', [GradeController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);
$router->get('/student/evaluation', [EvaluationController::class, 'show'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);

// Public Verification
$router->get('/verify/student-pass', [PublicVerificationController::class, 'verifyStudentPass']);
