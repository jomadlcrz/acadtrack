<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Set extends Model
{
    protected $table = 'sets';

    protected $fillable = [
        'set_name',
        'name',
        'program_id',
        'year_level',
        'set_code',
        'academic_term_id',
        'department_id',
        'status',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column) && in_array($column, ['name', 'sets.name'], true)) {
                    $column = ($column === 'name') ? 'set_name' : 'sets.set_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if (is_string($column) && in_array($column, ['name', 'sets.name'], true)) {
                    $column = ($column === 'name') ? 'set_name' : 'sets.set_name';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['set_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['set_name'] = $value;
    }

    public function getSetNameAttribute(): string
    {
        return (string) ($this->attributes['set_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setSetNameAttribute($value): void
    {
        $this->attributes['set_name'] = $value;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $name = $this->set_name;
        $array['set_name'] = $name;
        $array['name'] = $name;
        return $array;
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public static function deriveSetName(string $programAbbrev, int $yearLevel, string $setCode): string
    {
        return strtoupper(trim($programAbbrev)) . '-' . $yearLevel . strtoupper(trim($setCode));
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'set_id');
    }

    public static function getActiveByTerm(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('status', 'active')
            ->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByTermWithDetails(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->with(['department'])
            ->withCount('students')
            ->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByYearAndTerm(int $yearLevel, int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('year_level', $yearLevel)
            ->where('status', 'active')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getAssignedBySubject(int $subjectId, int $academicTermId): array
    {
        $subject = Subject::find($subjectId);
        if (!$subject) {
            return self::getActiveByTerm($academicTermId);
        }

        $query = self::where('academic_term_id', $academicTermId)->where('status', 'active');

        // Match sets by program and year level of the assigned subject
        $subQuery = self::where('academic_term_id', $academicTermId)->where('status', 'active');
        if (!empty($subject->program_id) && !empty($subject->year_level)) {
            $subQuery->where('program_id', $subject->program_id)
                     ->where('year_level', $subject->year_level);
        } elseif (!empty($subject->year_level)) {
            $subQuery->where('year_level', $subject->year_level);
        }
        $matchingIds = $subQuery->pluck('id')->toArray();

        // Also include sets of any students actively enrolled in this subject offering
        $enrolledSetIds = \Illuminate\Database\Capsule\Manager::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->where('enrollments.subject_id', $subjectId)
            ->where('enrollments.academic_term_id', $academicTermId)
            ->whereNotNull('students.set_id')
            ->pluck('students.set_id')
            ->toArray();

        $relevantIds = array_values(array_unique(array_merge($matchingIds, $enrolledSetIds)));
        if (!empty($relevantIds)) {
            $query->whereIn('id', $relevantIds);
        }

        return $query->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve sets assigned to this faculty member for a given term and optional subject.
     * Prioritizes explicit section assignments from `faculty_subjects.set_id`.
     */
    public static function getAssignedForFaculty(int $facultyId, int $academicTermId, ?int $subjectId = null): array
    {
        // 1. Direct query on faculty_subjects with assigned set_id
        $query = \Illuminate\Database\Capsule\Manager::table('faculty_subjects')
            ->join('sets', 'faculty_subjects.set_id', '=', 'sets.id')
            ->where('faculty_subjects.faculty_id', $facultyId)
            ->where('faculty_subjects.academic_term_id', $academicTermId)
            ->whereNotNull('faculty_subjects.set_id')
            ->where('sets.status', 'active');

        if ($subjectId !== null && $subjectId > 0) {
            $query->where('faculty_subjects.subject_id', $subjectId);
        }

        $assignedRows = $query->select('sets.*')
            ->distinct()
            ->orderBy('sets.year_level', 'asc')
            ->orderBy('sets.set_name', 'asc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        if (!empty($assignedRows)) {
            $enrolledCounts = self::getEnrolledCountsBySet($subjectId, $academicTermId);
            foreach ($assignedRows as &$s) {
                $s['enrolled_count'] = (int) ($enrolledCounts[$s['id']] ?? 0);
            }
            unset($s);

            return $assignedRows;
        }

        // 2. If no explicit section was assigned (legacy/unassigned set), check enrolled sets
        $enrolled = self::getEnrolledBySubject($subjectId, $academicTermId, $facultyId);
        if (!empty($enrolled)) {
            return $enrolled;
        }

        // 3. Fallback: If no enrollments yet, and subject is specified, return program + year_level sets of that subject
        if ($subjectId !== null && $subjectId > 0) {
            $subject = Subject::find($subjectId);
            if ($subject) {
                $subQuery = self::where('academic_term_id', $academicTermId)->where('status', 'active');
                if (!empty($subject->program_id) && !empty($subject->year_level)) {
                    $subQuery->where('program_id', $subject->program_id)
                             ->where('year_level', $subject->year_level);
                } elseif (!empty($subject->year_level)) {
                    $subQuery->where('year_level', $subject->year_level);
                } else {
                    return [];
                }
                return $subQuery->orderBy('year_level', 'asc')->orderBy('set_name', 'asc')->get()->toArray();
            }
        }

        return [];
    }

    /**
     * Retrieve enrolled student counts grouped by set_id for a subject/term.
     */
    public static function getEnrolledCountsBySet(?int $subjectId, int $academicTermId): array
    {
        $query = \Illuminate\Database\Capsule\Manager::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('student_details', 'student_details.user_id', '=', 'students.user_id')
            ->where('enrollments.academic_term_id', $academicTermId)
            ->where(function ($q) {
                $q->whereNotNull('students.set_id')
                  ->orWhereNotNull('student_details.set_id');
            });

        if ($subjectId !== null && $subjectId > 0) {
            $query->where('enrollments.subject_id', $subjectId);
        }

        return $query->selectRaw('COALESCE(students.set_id, student_details.set_id) as set_id, COUNT(DISTINCT students.id) as enrolled_count')
            ->groupBy('set_id')
            ->pluck('enrolled_count', 'set_id')
            ->toArray();
    }

    /**
     * Retrieve only sets that have active students enrolled in this specific subject offering.
     */
    public static function getEnrolledBySubject(?int $subjectId, int $academicTermId, ?int $facultyId = null): array
    {
        if ($subjectId === null || $subjectId <= 0) {
            if ($facultyId !== null && $facultyId > 0) {
                $assigned = Faculty::getAssignedSubjects($facultyId, $academicTermId);
                $subjectIds = array_column($assigned, 'id');
                if (empty($subjectIds)) {
                    return [];
                }
            } else {
                return [];
            }
        } else {
            $subjectIds = [$subjectId];
        }

        $enrolledCounts = \Illuminate\Database\Capsule\Manager::table('enrollments')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->leftJoin('student_details', 'student_details.user_id', '=', 'students.user_id')
            ->whereIn('enrollments.subject_id', $subjectIds)
            ->where('enrollments.academic_term_id', $academicTermId)
            ->where(function ($q) {
                $q->whereNotNull('students.set_id')
                  ->orWhereNotNull('student_details.set_id');
            })
            ->selectRaw('COALESCE(students.set_id, student_details.set_id) as set_id, COUNT(DISTINCT students.id) as enrolled_count')
            ->groupBy('set_id')
            ->pluck('enrolled_count', 'set_id')
            ->toArray();

        if (empty($enrolledCounts)) {
            return [];
        }

        $sets = self::whereIn('id', array_keys($enrolledCounts))
            ->where('academic_term_id', $academicTermId)
            ->where('status', 'active')
            ->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();

        foreach ($sets as &$s) {
            $s['enrolled_count'] = (int) ($enrolledCounts[$s['id']] ?? 0);
        }
        unset($s);

        return $sets;
    }
}
