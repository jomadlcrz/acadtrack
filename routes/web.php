<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\DepartmentController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Dean\FacultyAssignmentController;
use App\Controllers\Dean\SubjectController as DeanSubjectController;
use App\Controllers\Dean\SectionController;
use App\Controllers\Dean\GradeReviewController;
use App\Controllers\Faculty\SubjectController as FacultySubjectController;
use App\Controllers\Faculty\StudentController;
use App\Controllers\Faculty\GradingController;
use App\Controllers\Faculty\GradeSubmissionController;
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
$router->post('/admin/users/{id}/delete', [UserController::class, 'destroy'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->get('/admin/departments', [DepartmentController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/departments', [DepartmentController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/departments/{id}', [DepartmentController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->post('/admin/departments/{id}/delete', [DepartmentController::class, 'destroy'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);
$router->get('/admin/settings', [SettingsController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Admin'])]);
$router->post('/admin/settings', [SettingsController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()]);

// Dean routes
$router->get('/dean/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->get('/dean/faculty-assignments', [FacultyAssignmentController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/faculty-assignments/assign', [FacultyAssignmentController::class, 'assign'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/faculty-assignments/remove', [FacultyAssignmentController::class, 'remove'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->get('/dean/subjects', [DeanSubjectController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/subjects', [DeanSubjectController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/subjects/{id}', [DeanSubjectController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/subjects/{id}/delete', [DeanSubjectController::class, 'destroy'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->get('/dean/sections', [SectionController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin'])]);
$router->post('/dean/sections', [SectionController::class, 'store'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/sections/{id}', [SectionController::class, 'update'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
$router->post('/dean/sections/{id}/delete', [SectionController::class, 'destroy'], [new AuthMiddleware(), new RoleMiddleware(['Dean', 'Admin']), new CsrfMiddleware()]);
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
$router->get('/faculty/grading', [GradingController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Faculty'])]);
$router->post('/faculty/grading/save', [GradingController::class, 'save'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);
$router->post('/faculty/grading/submit', [GradingController::class, 'submit'], [new AuthMiddleware(), new RoleMiddleware(['Faculty']), new CsrfMiddleware()]);

// Student routes
$router->get('/student/dashboard', [DashboardController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);
$router->get('/student/grades', [GradeController::class, 'index'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);
$router->get('/student/evaluation', [EvaluationController::class, 'show'], [new AuthMiddleware(), new RoleMiddleware(['Student'])]);
