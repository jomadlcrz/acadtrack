<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Database;
use App\Models\Program;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Department;
use App\Models\AcademicTerm;
use PDO;

class ProgramCurriculumController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $programs = Program::with(['department', 'activeCurriculum'])
            ->orderBy('program_abbrev', 'asc')
            ->get();

        $selectedAbbrev = trim((string) $request->get('program', ''));
        if (empty($selectedAbbrev) && $programs->isNotEmpty()) {
            $selectedAbbrev = $programs->first()->program_abbrev;
        }

        $selectedProgram = $programs->firstWhere('program_abbrev', $selectedAbbrev);
        if (!$selectedProgram && $programs->isNotEmpty()) {
            $selectedProgram = $programs->first();
            $selectedAbbrev = $selectedProgram->program_abbrev;
        }

        $curriculum = null;
        $groupedSubjects = [];
        $totalSubjects = 0;
        $totalUnits = 0.0;

        if ($selectedProgram) {
            $curriculum = Curriculum::where('program_id', $selectedProgram->id)
                ->where('status', 'active')
                ->first();

            if (!$curriculum) {
                $curriculum = Curriculum::where('program_id', $selectedProgram->id)->first();
            }

            if ($curriculum) {
                $subjects = CurriculumSubject::where('curriculum_id', $curriculum->id)
                    ->orderBy('display_order', 'asc')
                    ->orderBy('subject_code', 'asc')
                    ->get();

                $totalSubjects = $subjects->count();

                // Group by year_level and semester
                $yearSortMap = [
                    'First Year' => 1, '1st Year' => 1,
                    'Second Year' => 2, '2nd Year' => 2,
                    'Third Year' => 3, '3rd Year' => 3,
                    'Fourth Year' => 4, '4th Year' => 4,
                    'Fifth Year' => 5, '5th Year' => 5,
                ];

                $tempGroups = [];
                foreach ($subjects as $sub) {
                    $totalUnits += (float) $sub->units;
                    $yl = $sub->year_level ?: 'Unassigned Year';
                    $sem = (int) $sub->semester;
                    $semLabel = match ($sem) {
                        1 => '1st Semester',
                        2 => '2nd Semester',
                        3 => 'Summer',
                        default => "Semester {$sem}",
                    };

                    $groupKey = "{$yl} · {$semLabel}";
                    if (!isset($tempGroups[$groupKey])) {
                        $tempGroups[$groupKey] = [
                            'year_level' => $yl,
                            'semester' => $sem,
                            'semester_label' => $semLabel,
                            'title' => $groupKey,
                            'sort_order' => ($yearSortMap[$yl] ?? 99) * 10 + $sem,
                            'subjects' => [],
                            'total_units' => 0.0,
                        ];
                    }

                    $tempGroups[$groupKey]['subjects'][] = $sub;
                    $tempGroups[$groupKey]['total_units'] += (float) $sub->units;
                }

                // Sort groups in academic progression order
                uasort($tempGroups, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
                $groupedSubjects = array_values($tempGroups);
            }
        }

        $html = (new View())->render('admin.program_curricula.index', [
            'programs' => $programs,
            'selectedProgram' => $selectedProgram,
            'selectedAbbrev' => $selectedAbbrev,
            'curriculum' => $curriculum,
            'groupedSubjects' => $groupedSubjects,
            'totalSubjects' => $totalSubjects,
            'totalUnits' => $totalUnits,
        ]);

        $response->html($html);
    }

    public function create(Request $request, Response $response, Session $session): void
    {
        $departments = Department::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $existingPrograms = Program::orderBy('program_abbrev', 'asc')->get();

        $html = (new View())->render('admin.program_curricula.create', [
            'departments' => $departments,
            'existingPrograms' => $existingPrograms,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $isJson = str_contains((string) $request->header('Content-Type'), 'application/json');
        $data = $isJson ? json_decode(file_get_contents('php://input'), true) ?? [] : $_POST;

        $programName = trim((string) ($data['program_name'] ?? ''));
        $programAbbrev = strtoupper(trim((string) ($data['program_abbrev'] ?? '')));
        $departmentId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
        $programType = trim((string) ($data['program_type'] ?? "Bachelor's Degree"));
        $programLength = trim((string) ($data['program_length'] ?? '4 Years'));
        $description = trim((string) ($data['description'] ?? '')) ?: null;
        $status = in_array($data['status'] ?? 'active', ['active', 'draft', 'archived'], true) ? $data['status'] : 'active';

        $subjectsInput = $data['subjects'] ?? $data['subjects_json'] ?? [];
        if (is_string($subjectsInput)) {
            $subjectsInput = json_decode($subjectsInput, true) ?? [];
        }

        if (empty($programName) || empty($programAbbrev)) {
            $this->respondError($isJson, $response, $session, 'Program Name and Program Abbreviation are required.');
            return;
        }

        // Check if program with abbreviation already exists
        $existing = Program::where('program_abbrev', $programAbbrev)->first();
        if ($existing) {
            $program = $existing;
            $program->update([
                'program_name' => $programName,
                'department_id' => $departmentId,
                'program_type' => $programType,
                'program_length' => $programLength,
                'description' => $description,
                'status' => $status,
            ]);
        } else {
            $program = Program::create([
                'program_name' => $programName,
                'program_abbrev' => $programAbbrev,
                'department_id' => $departmentId,
                'program_type' => $programType,
                'program_length' => $programLength,
                'description' => $description,
                'status' => $status,
            ]);
        }

        // Create or locate canonical curriculum for this program
        $curriculum = Curriculum::where('program_id', $program->id)->first();
        if (!$curriculum) {
            $curriculum = Curriculum::create([
                'program_id' => $program->id,
                'status' => $status,
            ]);
        } else {
            $curriculum->update([
                'status' => $status,
            ]);
        }

        // Active term for subject mapping if needed
        $activeTerm = AcademicTerm::getActive();
        $termId = $activeTerm['id'] ?? 1;

        $pdo = Database::getConnection();

        // Process subjects
        $insertedCount = 0;
        $order = 1;
        foreach ($subjectsInput as $row) {
            $code = strtoupper(trim((string) ($row['subject_code'] ?? $row['code'] ?? '')));
            $title = trim((string) ($row['descriptive_title'] ?? $row['title'] ?? $row['name'] ?? ''));
            $units = (float) ($row['units'] ?? 3.0);
            $yearLevel = trim((string) ($row['year_level'] ?? 'First Year'));
            $semRaw = $row['semester'] ?? 1;
            $semester = match ((string) $semRaw) {
                '1', '1st Semester' => 1,
                '2', '2nd Semester' => 2,
                '3', 'Summer' => 3,
                default => (int) $semRaw ?: 1,
            };
            $subjectType = trim((string) ($row['subject_type'] ?? 'GenEd Core'));
            $prereqs = is_array($row['prerequisites'] ?? null)
                ? implode(', ', array_filter($row['prerequisites']))
                : trim((string) ($row['prerequisites'] ?? ''));

            if (empty($code) || empty($title)) {
                continue;
            }

            $ylInt = match(true) {
                str_contains(strtolower($yearLevel), 'first') || $yearLevel === '1' => 1,
                str_contains(strtolower($yearLevel), 'second') || $yearLevel === '2' => 2,
                str_contains(strtolower($yearLevel), 'third') || $yearLevel === '3' => 3,
                str_contains(strtolower($yearLevel), 'fourth') || $yearLevel === '4' => 4,
                default => (int) $yearLevel ?: 1,
            };

            // Sync with subjects catalog table if it does not exist
            $stmtFindSubject = $pdo->prepare("SELECT id FROM subjects WHERE subject_code = :code LIMIT 1");
            $stmtFindSubject->execute(['code' => $code]);
            $subjectRow = $stmtFindSubject->fetch(PDO::FETCH_ASSOC);

            if (!$subjectRow) {
                $stmtInsertSub = $pdo->prepare("
                    INSERT INTO subjects (subject_code, descriptive_title, nature, year_level, semester, academic_term_id, is_archived, created_at, updated_at)
                    VALUES (:code, :title, 'Lecture', :yl, :sem, :tid, 0, NOW(), NOW())
                ");
                $stmtInsertSub->execute([
                    'code' => $code,
                    'title' => $title,
                    'yl' => $ylInt,
                    'sem' => $semester,
                    'tid' => $termId,
                ]);
                $subjectId = (int) $pdo->lastInsertId();
            } else {
                $subjectId = (int) $subjectRow['id'];
                $stmtUpdateSubYl = $pdo->prepare("UPDATE subjects SET year_level = :yl, semester = :sem WHERE id = :id AND year_level = 0");
                $stmtUpdateSubYl->execute(['yl' => $ylInt, 'sem' => $semester, 'id' => $subjectId]);
            }

            // Upsert into curriculum_subjects
            $stmtFindCS = $pdo->prepare("
                SELECT id FROM curriculum_subjects 
                WHERE curriculum_id = :cid AND subject_code = :code 
                LIMIT 1
            ");
            $stmtFindCS->execute(['cid' => $curriculum->id, 'code' => $code]);
            $csRow = $stmtFindCS->fetch(PDO::FETCH_ASSOC);

            if ($csRow) {
                $stmtUpdateCS = $pdo->prepare("
                    UPDATE curriculum_subjects 
                    SET subject_id = :sid, year_level = :yl, semester = :sem, 
                        descriptive_title = :title, units = :units, subject_type = :stype, 
                        prerequisites = :prereqs, display_order = :ord, updated_at = NOW()
                    WHERE id = :id
                ");
                $stmtUpdateCS->execute([
                    'sid' => $subjectId,
                    'yl' => $yearLevel,
                    'sem' => $semester,
                    'title' => $title,
                    'units' => $units,
                    'stype' => $subjectType,
                    'prereqs' => $prereqs ?: null,
                    'ord' => $order,
                    'id' => $csRow['id'],
                ]);
            } else {
                $stmtInsertCS = $pdo->prepare("
                    INSERT INTO curriculum_subjects 
                    (curriculum_id, subject_id, year_level, semester, subject_code, descriptive_title, units, subject_type, prerequisites, display_order, created_at, updated_at)
                    VALUES (:cid, :sid, :yl, :sem, :code, :title, :units, :stype, :prereqs, :ord, NOW(), NOW())
                ");
                $stmtInsertCS->execute([
                    'cid' => $curriculum->id,
                    'sid' => $subjectId,
                    'yl' => $yearLevel,
                    'sem' => $semester,
                    'code' => $code,
                    'title' => $title,
                    'units' => $units,
                    'stype' => $subjectType,
                    'prereqs' => $prereqs ?: null,
                    'ord' => $order,
                ]);
            }
            $insertedCount++;
            $order++;
        }

        $msg = "Curriculum for '{$program->program_abbrev}' successfully saved with {$insertedCount} subjects.";
        if ($isJson) {
            $response->json([
                'success' => true,
                'message' => $msg,
                'redirect' => url('/admin/program-curricula?program=' . urlencode($program->program_abbrev)),
            ]);
            return;
        }

        $session->flash('success', $msg);
        redirect('/admin/program-curricula?program=' . urlencode($program->program_abbrev));
    }

    public function exportCsv(Request $request, Response $response, Session $session, string $id): void
    {
        $program = Program::find((int) $id);
        if (!$program) {
            $program = Program::where('program_abbrev', $id)->first();
        }

        if (!$program) {
            $session->flash('error', 'Program not found.');
            redirect('/admin/program-curricula');
            return;
        }

        $curriculum = Curriculum::where('program_id', $program->id)->first();
        $subjects = $curriculum 
            ? CurriculumSubject::where('curriculum_id', $curriculum->id)->orderBy('year_level')->orderBy('semester')->orderBy('display_order')->get()
            : collect([]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $program->program_abbrev . '-Curriculum.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Year Level', 'Semester', 'Subject Code', 'Descriptive Title', 'Units', 'Subject Type', 'Pre-Requisite']);

        foreach ($subjects as $s) {
            $semLabel = match ((int) $s->semester) {
                1 => '1st Semester',
                2 => '2nd Semester',
                3 => 'Summer',
                default => (string) $s->semester,
            };
            fputcsv($out, [
                $s->year_level,
                $semLabel,
                $s->subject_code,
                $s->descriptive_title,
                $s->units,
                $s->subject_type,
                $s->prerequisites ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function downloadTemplateCsv(Request $request, Response $response): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Curriculum_Subjects_Template.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Year Level', 'Semester', 'Subject Code', 'Descriptive Title', 'Units', 'Subject Type', 'Pre-Requisite']);
        fputcsv($out, ['First Year', '1st Semester', 'IT101', 'Introduction to Computing', '3.0', 'GenEd Core', '']);
        fputcsv($out, ['First Year', '1st Semester', 'IT102', 'Computer Programming 1', '3.0', 'Major with Lab', '']);
        fputcsv($out, ['First Year', '2nd Semester', 'IT103', 'Computer Programming 2', '3.0', 'Major with Lab', 'IT102']);
        fputcsv($out, ['First Year', '2nd Semester', 'IT104', 'Data Structures and Algorithms', '3.0', 'Major with Lab', 'IT102']);
        fclose($out);
        exit;
    }

    private function respondError(bool $isJson, Response $response, Session $session, string $error): void
    {
        if ($isJson) {
            $response->status(422)->json([
                'success' => false,
                'message' => $error,
            ]);
            return;
        }

        $session->flash('error', $error);
        redirect('/admin/program-curricula/new');
    }
}
