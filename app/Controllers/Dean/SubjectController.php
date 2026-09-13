<?php

declare(strict_types=1);

namespace App\Controllers\Dean;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\SubjectRepository;
use App\Validators\SubjectValidator;

class SubjectController
{
    private SubjectRepository $subjectRepository;

    public function __construct()
    {
        $this->subjectRepository = new SubjectRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();
        $statusFilter = (string) $request->get('status', 'all');
        if (!in_array($statusFilter, ['all', 'active', 'archived'], true)) {
            $statusFilter = 'all';
        }

        $filterArg = $statusFilter === 'all' ? null : $statusFilter;
        $subjects = $this->subjectRepository->getByDean($academicTerm['id'] ?? 0, $filterArg);

        $html = (new View())->render('dean.subjects.index', [
            'subjects' => $subjects,
            'academicTerm' => $academicTerm,
            'statusFilter' => $statusFilter,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $session->flash('error', 'Curricular subjects are view-only. Academic curriculum authoring is managed by Administrators.');
        redirect('/dean/subjects');
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $session->flash('error', 'Curricular subjects are view-only. Academic curriculum authoring is managed by Administrators.');
        redirect('/dean/subjects');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        $session->flash('error', 'Curricular subjects are view-only. Academic curriculum authoring is managed by Administrators.');
        redirect('/dean/subjects');
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        $session->flash('error', 'Curricular subjects are view-only. Academic curriculum authoring is managed by Administrators.');
        redirect('/dean/subjects');
    }
}
