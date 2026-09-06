<?php

declare(strict_types=1);

namespace App\Validators;

class LoginValidator
{
    private array $errors = [];

    public function validate(array $data): bool
    {
        $this->errors = [];

        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format.';
        }

        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required.';
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
