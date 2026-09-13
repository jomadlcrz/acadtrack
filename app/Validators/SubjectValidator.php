<?php

declare(strict_types=1);

namespace App\Validators;

class SubjectValidator
{
    private array $errors = [];

    public function validate(array $data): bool
    {
        $this->errors = [];

        $code = trim((string) ($data['subject_code'] ?? $data['code'] ?? ''));
        if ($code === '') {
            $this->errors['code'] = 'Subject code is required.';
        } elseif (strlen($code) > 50) {
            $this->errors['code'] = 'Subject code must be 50 characters or less.';
        }

        $title = trim((string) ($data['descriptive_title'] ?? $data['name'] ?? ''));
        if ($title === '') {
            $this->errors['name'] = 'Descriptive title is required.';
        }

        if (empty($data['year_level'])) {
            $this->errors['year_level'] = 'Year level is required.';
        }

        if (empty($data['semester'])) {
            $this->errors['semester'] = 'Semester is required.';
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
