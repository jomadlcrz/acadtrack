<?php

declare(strict_types=1);

namespace App\Controllers\Dean;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\Section;

class SectionController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $termId = (int) ($academicTerm['id'] ?? 1);
        $sections = Section::getByTermWithDetails($termId);
        $departments = Department::getActive();

        $html = (new View())->render('dean.sections.index', [
            'sections' => $sections,
            'departments' => $departments,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $name = strtoupper(trim((string) $request->post('name', '')));
        $yearLevel = (int) $request->post('year_level', 1);
        $termId = (int) $request->post('academic_term_id', 0);
        $deptId = !empty($request->post('department_id')) ? (int) $request->post('department_id') : null;
        $status = in_array($request->post('status'), ['active', 'inactive'], true) ? $request->post('status') : 'active';
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if ($termId === 0) {
            $academicTerm = AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        if (empty($name)) {
            $session->flash('error', 'Section name is required.');
            redirect("/dean/sections{$semQuery}");
            return;
        }

        if (Section::where('name', $name)->where('academic_term_id', $termId)->exists()) {
            $session->flash('error', "Section '{$name}' already exists for this academic term.");
            redirect("/dean/sections{$semQuery}");
            return;
        }

        Section::create([
            'name' => $name,
            'year_level' => $yearLevel,
            'academic_term_id' => $termId,
            'department_id' => $deptId,
            'status' => $status,
        ]);

        $session->flash('success', "Section '{$name}' created successfully.");
        redirect("/dean/sections{$semQuery}");
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $section = Section::find((int) $id);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if (!$section) {
            $session->flash('error', 'Section not found.');
            redirect("/dean/sections{$semQuery}");
            return;
        }

        $name = strtoupper(trim((string) $request->post('name', '')));
        $yearLevel = (int) $request->post('year_level', $section->year_level);
        $deptId = !empty($request->post('department_id')) ? (int) $request->post('department_id') : null;
        $status = in_array($request->post('status'), ['active', 'inactive'], true) ? $request->post('status') : 'active';

        if (empty($name)) {
            $session->flash('error', 'Section name is required.');
            redirect("/dean/sections{$semQuery}");
            return;
        }

        if (Section::where('name', $name)
            ->where('academic_term_id', $section->academic_term_id)
            ->where('id', '!=', $section->id)
            ->exists()) {
            $session->flash('error', "Section '{$name}' already exists for this academic term.");
            redirect("/dean/sections{$semQuery}");
            return;
        }

        $section->update([
            'name' => $name,
            'year_level' => $yearLevel,
            'department_id' => $deptId,
            'status' => $status,
        ]);

        $session->flash('success', "Section '{$name}' updated successfully.");
        redirect("/dean/sections{$semQuery}");
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        $section = Section::find((int) $id);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if (!$section) {
            $session->flash('error', 'Section not found.');
            redirect("/dean/sections{$semQuery}");
            return;
        }

        $section->update(['status' => 'inactive']);
        $session->flash('success', "Section '{$section->name}' archived successfully.");
        redirect("/dean/sections{$semQuery}");
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        $section = Section::find((int) $id);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if (!$section) {
            $session->flash('error', 'Section not found.');
            redirect("/dean/sections{$semQuery}");
            return;
        }

        $section->update(['status' => 'active']);
        $session->flash('success', "Section '{$section->name}' restored to active status.");
        redirect("/dean/sections{$semQuery}");
    }
}
