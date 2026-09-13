<?php

declare(strict_types=1);

namespace App\Validators;

use App\Models\User;

class UserValidator
{
    private array $errors = [];

    public function validate(array $data, ?int $userId = null): bool
    {
        $this->errors = [];

        $firstName = trim((string) ($data['first_name'] ?? ''));
        if ($firstName === '') {
            $this->errors['first_name'] = 'First name is required.';
        }

        $lastName = trim((string) ($data['last_name'] ?? ''));
        if ($lastName === '') {
            $this->errors['last_name'] = 'Last name is required.';
        }

        $email = trim((string) ($data['email'] ?? ''));
        if ($email === '') {
            $this->errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address.';
        } else {
            $emailQuery = User::where('email', $email);
            if ($userId !== null) {
                $emailQuery->where('id', '!=', $userId);
            }
            if ($emailQuery->exists()) {
                $this->errors['email'] = "Email address '{$email}' is already registered to another account.";
            }
        }

        $role = (string) ($data['role'] ?? '');
        if (!in_array($role, ['Admin', 'Dean', 'Faculty', 'Student'], true)) {
            $this->errors['role'] = 'Please select a valid system role.';
        }

        if ($role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? ''));
            if ($studentNumber !== '') {
                $stuQuery = User::where('student_number', $studentNumber);
                if ($userId !== null) {
                    $stuQuery->where('id', '!=', $userId);
                }
                if ($stuQuery->exists()) {
                    $this->errors['student_number'] = "Student ID number '{$studentNumber}' is already registered to another user.";
                }
            }

            if (empty($data['set_id'])) {
                $this->errors['set_id'] = 'Assigned set is required for student accounts.';
            }
        } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
            if (empty($data['department_id'])) {
                $this->errors['department_id'] = 'Department / College is required for Faculty and Dean accounts.';
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
