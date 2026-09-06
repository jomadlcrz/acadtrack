<?php

declare(strict_types=1);

namespace App\Validators;

class GradeValidator
{
    private array $errors = [];
    private float $minGrade = 0;
    private float $maxGrade = 100;

    public function validate(array $data, float $min = 0, float $max = 100): bool
    {
        $this->errors = [];
        $this->minGrade = $min;
        $this->maxGrade = $max;

        if (!isset($data['grades']) || !is_array($data['grades'])) {
            $this->errors['grades'] = 'No grades provided.';
            return false;
        }

        foreach ($data['grades'] as $studentId => $grade) {
            if ($grade === '' || $grade === null) {
                continue;
            }

            $gradeValue = (float) $grade;
            if ($gradeValue < $this->minGrade || $gradeValue > $this->maxGrade) {
                $this->errors["grade_{$studentId}"] = "Grade must be between {$this->minGrade} and {$this->maxGrade}.";
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
