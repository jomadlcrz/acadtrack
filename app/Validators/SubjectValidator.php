<?php

declare(strict_types=1);

namespace App\Validators;

class SubjectValidator
{
    private array $errors = [];

    public function validate(array $data): bool
    {
        $this->errors = [];

        if (empty($data['code'])) {
            $this->errors['code'] = 'Subject code is required.';
        } elseif (strlen($data['code']) > 20) {
            $this->errors['code'] = 'Subject code must be 20 characters or less.';
        }

        if (empty($data['name'])) {
            $this->errors['name'] = 'Subject name is required.';
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
