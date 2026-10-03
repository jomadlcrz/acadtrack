<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Services\StudentRegistrationService;
use Illuminate\Database\Capsule\Manager as Capsule;

class RegistrationController
{
    private StudentRegistrationService $service;

    public function __construct()
    {
        $this->service = new StudentRegistrationService();
    }

    /** Registration is part of the student directory now. */
    public function index(Request $request, Response $response, Session $session): void
    {
        $termId = (int) $request->get('term_id', 0);
        redirect('/admin/students' . ($termId > 0 ? '?term_id=' . $termId : ''));
    }

    public function rollover(Request $request, Response $response, Session $session): void
    {
        $fromTermId = (int) $request->post('from_term_id', 0);

        try {
            $result = $this->service->rollover($fromTermId, (int) ($session->get('user')['id'] ?? 0) ?: null);
            $message = "Registered {$result['registered']} students for the next term";
            if ($result['completed'] > 0) {
                $message .= ", {$result['completed']} completed their program";
            }
            $message .= '.';
            if (!empty($result['skipped'])) {
                $reasons = array_count_values(array_column($result['skipped'], 'reason'));
                $parts = [];
                foreach ($reasons as $reason => $count) {
                    $parts[] = "{$count} skipped: {$reason}";
                }
                $message .= ' ' . implode(' ', $parts);
            }
            $session->flash('success', $message);
            redirect('/admin/students?term_id=' . $result['target_term_id']);
        } catch (\DomainException $e) {
            $session->flash('error', $e->getMessage());
            redirect('/admin/students?term_id=' . $fromTermId);
        }
    }
}
