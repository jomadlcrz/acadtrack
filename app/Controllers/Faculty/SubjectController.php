<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Faculty;
use App\Models\AcademicTerm;

class SubjectController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $academicTerm = AcademicTerm::getActive();
        $subjects = Faculty::getAssignedSubjects((int) $user['id'], $academicTerm['id'] ?? 0);

        $html = (new View())->render('faculty.subjects.index', [
            'subjects' => $subjects,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }
}
