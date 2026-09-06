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
        $subjects = $this->subjectRepository->getByDean($academicTerm['id'] ?? 0);

        $html = (new View())->render('dean.subjects.index', [
            'subjects' => $subjects,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $validator = new SubjectValidator();
        $data = $request->all();
        $data['academic_term_id'] = (new \App\Models\AcademicTerm())->getActive()['id'] ?? 0;

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
                $code = htmlspecialchars((string) ($data['code'] ?? ''));
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
        try {
            $this->subjectRepository->update((int) $id, $data);
            $session->flash('success', 'Subject updated successfully.');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                $code = htmlspecialchars((string) ($data['code'] ?? ''));
                $session->flash('error', "Subject code '{$code}' already exists for this academic term.");
            } else {
                $session->flash('error', 'Database error: unable to update subject.');
            }
        }
        redirect('/dean/subjects');
    }

    public function destroy(Request $request, Response $response, Session $session, string $id): void
    {
        try {
            $this->subjectRepository->delete((int) $id);
            $session->flash('success', 'Subject deleted.');
        } catch (\PDOException $e) {
            $session->flash('error', 'Cannot delete subject because it has linked faculty assignments or enrollments.');
        }
        redirect('/dean/subjects');
    }
}
