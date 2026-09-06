<?php

declare(strict_types=1);

namespace App\Controllers\Dean;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\GradingSheetRepository;
use App\Services\GradingService;

class GradeReviewController
{
    private GradingSheetRepository $gradingSheetRepository;
    private GradingService $gradingService;

    public function __construct()
    {
        $this->gradingSheetRepository = new GradingSheetRepository();
        $this->gradingService = new GradingService();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();
        $pendingSheets = $this->gradingSheetRepository->getPendingReview($academicTerm['id'] ?? 0);

        $html = (new View())->render('dean.grade-review.index', [
            'gradingSheets' => $pendingSheets,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }

    public function approve(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');

        try {
            $this->gradingService->approveGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet approved.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect('/dean/grade-review');
    }

    public function returnToFaculty(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');

        try {
            $this->gradingService->returnGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet returned to faculty.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect('/dean/grade-review');
    }
}
