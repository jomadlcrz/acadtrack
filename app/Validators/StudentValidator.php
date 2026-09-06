<?php

declare(strict_types=1);

namespace App\Validators;

class StudentValidator
{
    private array $errors = [];

    public function validate(array $data): bool
    {
        $this->errors = [];

        if (empty($data['first_name'])) {
            $this->errors['first_name'] = 'First name is required.';
        }

        if (empty($data['last_name'])) {
            $this->errors['last_name'] = 'Last name is required.';
        }

        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format.';
        }

        if (!empty($data['student_number'])) {
            $studentNumber = trim((string) $data['student_number']);
            $query = \App\Models\User::where('student_number', $studentNumber);
            if (!empty($data['id'])) {
                $query->where('id', '!=', $data['id']);
            }
            if ($query->exists()) {
                $this->errors['student_number'] = 'Student number is already taken.';
            }
        }

        if (empty($data['section_id'])) {
            $this->errors['section_id'] = 'Section is required.';
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
