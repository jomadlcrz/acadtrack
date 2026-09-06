<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\GradingSheetRepository;
use App\Services\GradingService;

class GradeSubmissionController
{
    private GradingService $gradingService;

    public function __construct()
    {
        $this->gradingService = new GradingService();
    }

    public function submit(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');

        try {
            $this->gradingService->submitGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet submitted for review.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect('/faculty/grading');
    }
}
