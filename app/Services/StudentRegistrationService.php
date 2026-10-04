<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\Program;
use App\Models\Set;
use App\Models\Student;
use App\Models\StudentDetail;
use App\Models\StudentTermRegistration;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Term-level student registration: who is enrolled in which term, at what year level,
 * in which set, as Regular or Irregular. The students/student_details profile columns
 * mirror the student's latest registration so legacy pages keep working.
 */
class StudentRegistrationService
{
    /**
     * @throws \DomainException when the registration breaks a school rule
     */
    public function register(
        int $studentId,
        int $termId,
        string $status,
        int $yearLevel,
        ?int $setId = null,
        ?int $registeredBy = null,
        bool $log = true
    ): StudentTermRegistration {
        $student = Student::find($studentId);
        if (!$student) {
            throw new \DomainException('Student record not found.');
        }

        $term = AcademicTerm::find($termId);
        if (!$term) {
            throw new \DomainException('Academic term not found.');
        }
        if ((bool) ($term->is_closed ?? false)) {
            throw new \DomainException('Cannot register a student in a closed academic term.');
        }

        $status = Student::normalizeStatus($status);
        $setId = Student::resolveSetId($status, $setId);
        $programId = $this->programOf($student);

        if ($yearLevel < 1) {
            throw new \DomainException('Year level must be at least 1.');
        }

        if (Student::requiresSet($status)) {
            if (empty($setId)) {
                throw new \DomainException('A Regular student must be registered in a set.');
            }
            $set = Set::find($setId);
            if (!$set || (int) $set->academic_term_id !== $termId) {
                throw new \DomainException('The selected set does not belong to this academic term.');
            }
            if ((int) $set->year_level !== $yearLevel) {
                throw new \DomainException("Set '{$set->set_name}' is for Year {$set->year_level}, not Year {$yearLevel}.");
            }
            if (!empty($set->program_id)) {
                if ($programId !== null && (int) $set->program_id !== $programId) {
                    throw new \DomainException("Set '{$set->set_name}' belongs to a different program than the student's.");
                }
                $programId = (int) $set->program_id;
            }
        }

        $length = $this->programYears($programId);
        if ($length !== null && $yearLevel > $length) {
            throw new \DomainException("Year {$yearLevel} exceeds the program length of {$length} years.");
        }

        $registration = Capsule::connection()->transaction(function () use ($studentId, $termId, $programId, $setId, $yearLevel, $status, $registeredBy) {
            $registration = StudentTermRegistration::updateOrCreate(
                ['student_id' => $studentId, 'academic_term_id' => $termId],
                [
                    'program_id' => $programId,
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $status,
                    'registration_status' => StudentTermRegistration::STATE_ENROLLED,
                    'registered_by' => $registeredBy,
                ]
            );
            $this->mirrorLatest($studentId);
            return $registration;
        });

        if ($log) {
            $detail = StudentDetail::where('user_id', $student->user_id)->first();
            $name = $detail ? trim($detail->first_name . ' ' . $detail->last_name) : "student #{$studentId}";
            $setName = $setId ? Set::where('id', $setId)->value('set_name') : null;
            ActivityLogService::record([
                'category' => ActivityLogService::CATEGORY_REGISTRATION,
                'action' => 'Student Registered',
                'target_type' => 'student',
                'target_id' => $studentId,
                'target_label' => $name,
                'summary' => "Registered {$name} for " . $this->termLabel($term) . " as {$status}, Year {$yearLevel}" . ($setName ? ", set {$setName}" : '') . '.',
            ]);
        }

        return $registration;
    }

    /** The term that follows $termId: next semester of the same year, else semester 1 of the next school year. */
    public function nextTerm(int $termId): ?AcademicTerm
    {
        $term = AcademicTerm::find($termId);
        if (!$term) {
            return null;
        }

        $sameYear = AcademicTerm::where('academic_year_id', $term->academic_year_id)
            ->where('semester', (int) $term->semester + 1)
            ->where('is_archived', 0)
            ->first();
        if ($sameYear) {
            return $sameYear;
        }

        $year = Capsule::table('academic_years')->where('id', $term->academic_year_id)->first();
        if (!$year) {
            return null;
        }
        $nextYear = Capsule::table('academic_years')
            ->where('school_year', '>', $year->school_year)
            ->orderBy('school_year')
            ->first();
        if (!$nextYear) {
            return null;
        }

        return AcademicTerm::where('academic_year_id', $nextYear->id)
            ->where('semester', 1)
            ->where('is_archived', 0)
            ->first();
    }

    /**
     * Carry every enrolled student of $fromTermId into the next term.
     * Regular students move to the matching set (year level +1 on a new school year) and are
     * loaded with that set's curriculum subjects. Irregular students stay Irregular with no set
     * and pick subjects individually. Students past the program length are marked completed.
     *
     * @return array{target_term_id:int, registered:int, completed:int, skipped:array<int,array{student_id:int,reason:string}>}
     */
    public function rollover(int $fromTermId, ?int $registeredBy = null): array
    {
        $from = AcademicTerm::find($fromTermId);
        $to = $this->nextTerm($fromTermId);
        if (!$from || !$to) {
            throw new \DomainException('No next academic term exists yet. Create it before rolling students over.');
        }
        if ((bool) ($to->is_closed ?? false)) {
            throw new \DomainException('The next academic term is closed.');
        }

        $newSchoolYear = (int) $from->academic_year_id !== (int) $to->academic_year_id;
        $result = ['target_term_id' => (int) $to->id, 'registered' => 0, 'completed' => 0, 'skipped' => []];

        $registrations = StudentTermRegistration::where('academic_term_id', $fromTermId)
            ->where('registration_status', StudentTermRegistration::STATE_ENROLLED)
            ->get();

        foreach ($registrations as $reg) {
            $studentId = (int) $reg->student_id;

            $accountStatus = Capsule::table('students')->join('users', 'users.id', '=', 'students.user_id')
                ->where('students.id', $studentId)->value('users.status');
            if ($accountStatus !== 'active') {
                $result['skipped'][] = ['student_id' => $studentId, 'reason' => 'Account is inactive.'];
                continue;
            }

            if (StudentTermRegistration::where('student_id', $studentId)->where('academic_term_id', $to->id)->exists()) {
                $result['skipped'][] = ['student_id' => $studentId, 'reason' => 'Already registered for the next term.'];
                continue;
            }

            $year = (int) $reg->year_level + ($newSchoolYear ? 1 : 0);
            $length = $this->programYears($reg->program_id !== null ? (int) $reg->program_id : null);
            if ($length !== null && $year > $length) {
                $reg->update(['registration_status' => StudentTermRegistration::STATE_COMPLETED]);
                $result['completed']++;
                continue;
            }

            $setId = null;
            if (Student::requiresSet($reg->status)) {
                $setId = $this->matchingSet($reg, $year, (int) $to->id);
                if ($setId === null) {
                    $result['skipped'][] = ['student_id' => $studentId, 'reason' => "No Year {$year} set exists in the next term to move this student into."];
                    continue;
                }
            }

            try {
                $this->register($studentId, (int) $to->id, (string) $reg->status, $year, $setId, $registeredBy, false);
                if ($setId !== null) {
                    $this->enrollCurriculumLoad($studentId, $setId, $to);
                }
                $result['registered']++;
            } catch (\DomainException $e) {
                $result['skipped'][] = ['student_id' => $studentId, 'reason' => $e->getMessage()];
            }
        }

        ActivityLogService::record([
            'category' => ActivityLogService::CATEGORY_REGISTRATION,
            'action' => 'Term Rollover',
            'target_type' => 'term',
            'target_id' => (int) $to->id,
            'target_label' => $this->termLabel($to),
            'summary' => 'Rolled students over from ' . $this->termLabel($from) . ' to ' . $this->termLabel($to)
                . ": {$result['registered']} registered, {$result['completed']} completed, " . count($result['skipped']) . ' skipped.',
        ]);

        return $result;
    }

    private function termLabel(AcademicTerm $term): string
    {
        return trim((string) $term->school_year . ' - ' . semester_label($term->semester));
    }

    /** Enroll a Regular student in every active curriculum subject of their set's program, year level and semester. */
    public function enrollCurriculumLoad(int $studentId, int $setId, AcademicTerm $term): int
    {
        $set = Set::find($setId);
        if (!$set || empty($set->program_id)) {
            return 0;
        }

        $subjectIds = Capsule::table('subjects')
            ->where('academic_term_id', $term->id)
            ->where('program_id', $set->program_id)
            ->where('year_level', $set->year_level)
            ->where('semester', $term->semester)
            ->where('is_archived', 0)
            ->pluck('id');

        foreach ($subjectIds as $subjectId) {
            Student::enroll($studentId, (int) $subjectId, (int) $term->id);
        }
        return count($subjectIds);
    }

    private function matchingSet(StudentTermRegistration $reg, int $year, int $toTermId): ?int
    {
        $current = $reg->set_id ? Set::find((int) $reg->set_id) : null;
        if (!$current || empty($current->set_code)) {
            return null;
        }

        $query = Set::where('academic_term_id', $toTermId)
            ->where('status', 'active')
            ->where('year_level', $year)
            ->where('set_code', $current->set_code);
        if ($reg->program_id) {
            $query->where('program_id', $reg->program_id);
        }
        $match = $query->first();
        return $match ? (int) $match->id : null;
    }

    private function programOf(Student $student): ?int
    {
        $programId = StudentDetail::where('user_id', $student->user_id)->value('program_id');
        return $programId !== null ? (int) $programId : null;
    }

    private function programYears(?int $programId): ?int
    {
        if ($programId === null) {
            return null;
        }
        $length = Program::where('id', $programId)->value('program_length');
        return $length !== null && preg_match('/\d+/', (string) $length, $m) ? (int) $m[0] : null;
    }

    /** Keep the legacy profile columns equal to the student's latest registration. */
    private function mirrorLatest(int $studentId): void
    {
        $latest = StudentTermRegistration::where('student_id', $studentId)->orderBy('academic_term_id', 'desc')->first();
        if (!$latest) {
            return;
        }

        $profile = [
            'set_id' => $latest->set_id,
            'year_level' => $latest->year_level,
            'status' => $latest->status,
        ];
        $student = Student::find($studentId);
        $student->update($profile);
        StudentDetail::where('user_id', $student->user_id)->update($profile + ['program_id' => $latest->program_id]);
    }
}
