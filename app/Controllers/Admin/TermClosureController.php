<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\TermClosureService;

class TermClosureController
{
    private TermClosureService $service;

    public function __construct()
    {
        $this->service = new TermClosureService();
    }

    /**
     * Term closure dashboard & lifecycle management page.
     */
    public function index(Request $request, Response $response, Session $session): void
    {
        $closures = $this->service->getClosures();

        // Calculate summary metrics
        $totalTerms = count($closures);
        $closedTerms = count(array_filter($closures, fn($t) => $t['is_closed']));
        $activeTerms = array_filter($closures, fn($t) => $t['is_active']);
        $activeTerm = !empty($activeTerms) ? reset($activeTerms) : null;

        $html = (new View())->render('admin.academic_terms.closure', [
            'closures' => $closures,
            'totalTerms' => $totalTerms,
            'closedTerms' => $closedTerms,
            'activeTerm' => $activeTerm,
        ]);

        $response->html($html);
    }

    /**
     * JSON endpoint for pre-closure audit preview modal.
     */
    public function preview(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $preview = $this->service->getTermPreview($termId);

        if (!$preview) {
            $response->json(['error' => 'Academic term not found.'], 404);
            return;
        }

        $response->json($preview);
    }

    /**
     * Close an academic term.
     */
    public function close(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $user = $session->get('user');
        $userId = (int) ($user['id'] ?? 1);

        $reason = trim((string) $request->post('closure_reason', ''));
        $activateNext = (bool) $request->post('activate_next_semester', false);

        try {
            $result = $this->service->closeTerm($termId, $userId, $reason, $activateNext);
            $session->flash('success', $result['message']);
        } catch (\Throwable $e) {
            $session->flash('error', 'Failed to close term: ' . $e->getMessage());
        }

        redirect('/admin/academic-terms/closure');
    }

    /**
     * Reopen an academic term.
     */
    public function reopen(Request $request, Response $response, Session $session, string $id): void
    {
        $termId = (int) $id;
        $user = $session->get('user');
        $userId = (int) ($user['id'] ?? 1);

        $reason = trim((string) $request->post('reopen_reason', ''));

        if (empty($reason)) {
            $session->flash('error', 'A valid reason is required to reopen an academic term.');
            redirect('/admin/academic-terms/closure');
            return;
        }

        try {
            $result = $this->service->reopenTerm($termId, $userId, $reason);
            $session->flash('success', $result['message']);
        } catch (\Throwable $e) {
            $session->flash('error', 'Failed to reopen term: ' . $e->getMessage());
        }

        redirect('/admin/academic-terms/closure');
    }

    /**
     * Toggle individual grading period open/closed state.
     */
    public function togglePeriod(Request $request, Response $response, Session $session, string $id): void
    {
        $periodId = (int) $id;
        $status = trim((string) $request->post('status', 'closed'));
        $reason = trim((string) $request->post('reason', ''));

        try {
            $result = $this->service->togglePeriodState($periodId, $status, $reason ?: null);
            $session->flash('success', $result['message']);
        } catch (\Throwable $e) {
            $session->flash('error', 'Failed to update grading period: ' . $e->getMessage());
        }

        $termId = (int) $request->post('term_id', 0);
        redirect('/admin/academic-terms/closure');
    }
}
