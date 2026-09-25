<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\Program;
use App\Models\Set;
use App\Models\Student;

class SetController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);

        $sets = Set::where('academic_term_id', $termId)
            ->with(['program', 'department'])
            ->withCount('students')
            ->orderBy('year_level', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $programs = Program::where('status', 'active')
            ->orderBy('program_abbrev', 'asc')
            ->get();

        $departments = Department::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $userRole = (string) ($session->get('user')['role'] ?? '');
        $isReadOnly = (strcasecmp($userRole, 'Admin') !== 0);

        $html = (new View())->render('admin.sets.index', [
            'sets' => $sets,
            'programs' => $programs,
            'departments' => $departments,
            'activeTerm' => $activeTerm,
            'isReadOnly' => $isReadOnly,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $isJson = str_contains((string) $request->header('Content-Type'), 'application/json');
        if (strcasecmp((string) ($session->get('user')['role'] ?? ''), 'Admin') !== 0) {
            $this->respondError($isJson, $response, $session, 'Unauthorized. Sets management is read-only for Deans.');
            return;
        }

        $data = $isJson ? json_decode(file_get_contents('php://input'), true) ?? [] : $_POST;

        $programId = (int) ($data['program_id'] ?? 0);
        $program = Program::find($programId);

        if (!$program) {
            $this->respondError($isJson, $response, $session, 'Valid program selection is required.');
            return;
        }

        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);
        $deptId = $program->department_id;

        $termClosureService = new \App\Services\TermClosureService();
        if ($termClosureService->isTermClosed($termId)) {
            $this->respondError($isJson, $response, $session, 'Cannot create sections. This academic term is officially closed and sealed.');
            return;
        }

        $createMode = $data['create_mode'] ?? 'all'; // 'all' or 'custom'
        $itemsToCreate = [];

        if ($createMode === 'all') {
            $selectedYears = $data['selected_years'] ?? [1, 2, 3, 4];
            if (is_string($selectedYears)) {
                $selectedYears = explode(',', $selectedYears);
            }
            $rawCodes = (string) ($data['set_codes'] ?? '');
            $codes = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,\n\s]+/', strtoupper($rawCodes))))));

            if (empty($codes)) {
                $this->respondError($isJson, $response, $session, 'Please provide at least one section code (e.g. A, B).');
                return;
            }

            foreach ($selectedYears as $yl) {
                $ylInt = (int) $yl;
                if ($ylInt >= 1 && $ylInt <= 5) {
                    foreach ($codes as $code) {
                        $itemsToCreate[] = [
                            'year_level' => $ylInt,
                            'set_code' => $code,
                        ];
                    }
                }
            }
        } else {
            // custom mode per year
            $customCodes = $data['custom_codes'] ?? [];
            for ($ylInt = 1; $ylInt <= 4; $ylInt++) {
                $raw = (string) ($customCodes[$ylInt] ?? '');
                $codes = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,\n\s]+/', strtoupper($raw))))));
                foreach ($codes as $code) {
                    $itemsToCreate[] = [
                        'year_level' => $ylInt,
                        'set_code' => $code,
                    ];
                }
            }
        }

        if (empty($itemsToCreate)) {
            $this->respondError($isJson, $response, $session, 'No section codes specified to generate.');
            return;
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($itemsToCreate as $item) {
            $derivedName = Set::deriveSetName($program->program_abbrev, $item['year_level'], $item['set_code']);

            $exists = Set::where('set_name', $derivedName)
                ->where('academic_term_id', $termId)
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            Set::create([
                'set_name' => $derivedName,
                'program_id' => $program->id,
                'year_level' => $item['year_level'],
                'set_code' => $item['set_code'],
                'academic_term_id' => $termId,
                'department_id' => $deptId,
                'status' => 'active',
            ]);
            $createdCount++;
        }

        $msg = $createdCount > 1
            ? "Created {$createdCount} sections successfully."
            : ($createdCount === 1 ? "Section created successfully." : "No new sections were added (sections already exist).");

        if ($skippedCount > 0 && $createdCount > 0) {
            $msg .= " ({$skippedCount} duplicate sections skipped).";
        }

        if ($isJson) {
            $response->json([
                'success' => true,
                'count' => $createdCount,
                'message' => $msg,
            ]);
            return;
        }

        $session->flash('success', $msg);
        redirect('/admin/sets');
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        if (strcasecmp((string) ($session->get('user')['role'] ?? ''), 'Admin') !== 0) {
            $session->flash('error', 'Unauthorized. Sets management is read-only for Deans.');
            redirect('/admin/sets');
            return;
        }

        $set = Set::with('program')->find((int) $id);
        if (!$set) {
            $session->flash('error', 'Section not found.');
            redirect('/admin/sets');
            return;
        }

        $termClosureService = new \App\Services\TermClosureService();
        if ($termClosureService->isTermClosed((int) $set->academic_term_id)) {
            $session->flash('error', 'Cannot modify sections. This academic term is officially closed and sealed.');
            redirect('/admin/sets');
            return;
        }

        $setCode = strtoupper(trim((string) $request->post('set_code', $set->set_code ?? '')));
        $status = in_array($request->post('status'), ['active', 'inactive'], true) ? $request->post('status') : 'active';

        if (empty($setCode)) {
            $session->flash('error', 'Section code cannot be empty.');
            redirect('/admin/sets');
            return;
        }

        $programAbbrev = $set->program ? $set->program->program_abbrev : explode('-', $set->set_name)[0];
        $newName = Set::deriveSetName($programAbbrev, (int) $set->year_level, $setCode);

        // Check uniqueness in same term
        $conflict = Set::where('set_name', $newName)
            ->where('academic_term_id', $set->academic_term_id)
            ->where('id', '!=', $set->id)
            ->exists();

        if ($conflict) {
            $session->flash('error', "Section '{$newName}' already exists in this academic term.");
            redirect('/admin/sets');
            return;
        }

        $set->update([
            'set_code' => $setCode,
            'set_name' => $newName,
            'status' => $status,
        ]);

        $session->flash('success', "Section updated to '{$newName}'.");
        redirect('/admin/sets');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        if (strcasecmp((string) ($session->get('user')['role'] ?? ''), 'Admin') !== 0) {
            $session->flash('error', 'Unauthorized. Sets management is read-only for Deans.');
            redirect('/admin/sets');
            return;
        }

        $set = Set::find((int) $id);
        if (!$set) {
            $session->flash('error', 'Section not found.');
            redirect('/admin/sets');
            return;
        }

        $termClosureService = new \App\Services\TermClosureService();
        if ($termClosureService->isTermClosed((int) $set->academic_term_id)) {
            $session->flash('error', 'Cannot modify sections. This academic term is officially closed and sealed.');
            redirect('/admin/sets');
            return;
        }

        $set->update(['status' => 'inactive']);
        $session->flash('success', "Section '{$set->name}' archived successfully.");
        redirect('/admin/sets');
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        if (strcasecmp((string) ($session->get('user')['role'] ?? ''), 'Admin') !== 0) {
            $session->flash('error', 'Unauthorized. Sets management is read-only for Deans.');
            redirect('/admin/sets');
            return;
        }

        $set = Set::find((int) $id);
        if (!$set) {
            $session->flash('error', 'Section not found.');
            redirect('/admin/sets');
            return;
        }

        $termClosureService = new \App\Services\TermClosureService();
        if ($termClosureService->isTermClosed((int) $set->academic_term_id)) {
            $session->flash('error', 'Cannot modify sections. This academic term is officially closed and sealed.');
            redirect('/admin/sets');
            return;
        }

        $set->update(['status' => 'active']);
        $session->flash('success', "Section '{$set->name}' restored to active status.");
        redirect('/admin/sets');
    }

    public function bulkArchive(Request $request, Response $response, Session $session): void
    {
        $isJson = str_contains((string) $request->header('Content-Type'), 'application/json');
        if (strcasecmp((string) ($session->get('user')['role'] ?? ''), 'Admin') !== 0) {
            $this->respondError($isJson, $response, $session, 'Unauthorized. Sets management is read-only for Deans.');
            return;
        }

        $data = $isJson ? json_decode(file_get_contents('php://input'), true) ?? [] : $_POST;

        $ids = $data['ids'] ?? [];
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            $this->respondError($isJson, $response, $session, 'No sets selected.');
            return;
        }

        $archived = 0;
        $termClosureService = new \App\Services\TermClosureService();

        foreach ($ids as $setId) {
            $set = Set::find($setId);
            if (!$set) continue;
            if ($termClosureService->isTermClosed((int) $set->academic_term_id)) {
                continue;
            }

            // Strict zero hard delete: archive by updating status to inactive
            $set->update(['status' => 'inactive']);
            $archived++;
        }

        $msg = "{$archived} " . ($archived === 1 ? 'set' : 'sets') . ' archived successfully.';

        if ($isJson) {
            $response->json(['success' => true, 'message' => $msg]);
            return;
        }

        $session->flash('success', $msg);
        redirect('/admin/sets');
    }

    private function respondError(bool $isJson, Response $response, Session $session, string $error): void
    {
        if ($isJson) {
            $response->status(422)->json(['success' => false, 'message' => $error]);
            return;
        }

        $session->flash('error', $error);
        redirect('/admin/sets');
    }
}
