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
        $validator = new SubjectValidator();
        $data = $request->all();
        $data['academic_term_id'] = (new \App\Models\AcademicTerm())->getActive()['id'] ?? 0;
        $data['subject_code'] = trim((string) ($data['subject_code'] ?? $data['code'] ?? ''));
        $data['descriptive_title'] = trim((string) ($data['descriptive_title'] ?? $data['name'] ?? ''));
        $data['code'] = $data['subject_code'];
        $data['name'] = $data['descriptive_title'];

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect('/dean/subjects');
            return;
        }

        try {
            $this->subjectRepository->create($data);
            $session->flash('success', 'Subject created successfully.');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                $code = htmlspecialchars((string) ($data['subject_code'] ?? ''));
                $session->flash('error', "Subject code '{$code}' already exists for this academic term.");
            } else {
                $session->flash('error', 'Database error: unable to save subject.');
            }
        }
        redirect('/dean/subjects');
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $data = $request->all();
        if (isset($data['subject_code']) || isset($data['code'])) {
            $data['subject_code'] = trim((string) ($data['subject_code'] ?? $data['code'] ?? ''));
            $data['code'] = $data['subject_code'];
        }
        if (isset($data['descriptive_title']) || isset($data['name'])) {
            $data['descriptive_title'] = trim((string) ($data['descriptive_title'] ?? $data['name'] ?? ''));
            $data['name'] = $data['descriptive_title'];
        }

        try {
            $this->subjectRepository->update((int) $id, $data);
            $session->flash('success', 'Subject updated successfully.');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                $code = htmlspecialchars((string) ($data['subject_code'] ?? $data['code'] ?? ''));
                $session->flash('error', "Subject code '{$code}' already exists for this academic term.");
            } else {
                $session->flash('error', 'Database error: unable to update subject.');
            }
        }
        redirect('/dean/subjects');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        try {
            $this->subjectRepository->archive((int) $id);
            $session->flash('success', 'Subject archived successfully. Academic records and grades remain intact.');
        } catch (\Exception $e) {
            $session->flash('error', 'Failed to archive subject.');
        }
        redirect('/dean/subjects');
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        try {
            $this->subjectRepository->restore((int) $id);
            $session->flash('success', 'Subject restored to active curriculum catalog.');
        } catch (\Exception $e) {
            $session->flash('error', 'Failed to restore subject.');
        }
        redirect('/dean/subjects');
    }
}
