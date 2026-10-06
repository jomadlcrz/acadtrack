<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use InvalidArgumentException;

/**
 * Bulk-creates Faculty and Dean accounts from parsed spreadsheet rows.
 *
 * A spreadsheet carries the department as free text rather than a foreign key, so
 * it is matched against the department abbreviation or full name. An unmatched
 * value rejects the row instead of quietly creating an account with no
 * department attached, which is what the create form's required select prevents.
 */
class PersonnelImportService
{
    /** Admin stays manual-only: administrator accounts are not editable after creation. */
    public const IMPORTABLE_ROLES = ['Faculty', 'Dean'];

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array{total:int,created:int,failed:int,errors:array<int,array{row:int,name:string,message:string}>,credentials:array<string,string>}
     */
    public function import(array $rows, string $role): array
    {
        if (!in_array($role, self::IMPORTABLE_ROLES, true)) {
            throw new InvalidArgumentException('Spreadsheet import is not available for the ' . $role . ' role.');
        }

        $departments = $this->departmentIndex();
        $created = 0;
        $errors = [];
        $credentials = [];
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            $departmentLabel = trim((string) ($row['department'] ?? ''));
            $name = trim($firstName . ' ' . $lastName);

            if ($firstName === '' || $lastName === '') {
                $errors[] = $this->rowError($rowNumber, $name, 'First name and last name are required.');
                continue;
            }

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $this->rowError($rowNumber, $name, "Invalid email address '{$email}'.");
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = $this->rowError($rowNumber, $name, "Email '{$email}' is already registered.");
                continue;
            }

            if ($departmentLabel === '') {
                $errors[] = $this->rowError($rowNumber, $name, 'Department is required.');
                continue;
            }

            $departmentId = $departments[$this->normalizeLabel($departmentLabel)] ?? null;

            if (!$departmentId) {
                $errors[] = $this->rowError($rowNumber, $name, "Unknown department '{$departmentLabel}'.");
                continue;
            }

            try {
                $plainPassword = User::generateRandomPassword();
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'student_number' => null,
                    'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
                    'role' => $role,
                    'status' => 'active',
                    'force_password_change' => 1,
                ]);

                if (!$user || !$user->id) {
                    $errors[] = $this->rowError($rowNumber, $name, 'Account could not be created.');
                    continue;
                }

                Faculty::updateOrCreate(
                    ['user_id' => (int) $user->id],
                    ['department_id' => $departmentId]
                );

                // Personnel are onboarded with the same credentials email the single-account form sends.
                // Bulk imports never send SMTP per row: an unreachable mail server blocks until
                // max_execution_time and the request dies with fatal-error HTML instead of JSON.
                // The temporary password is returned so the admin can distribute it instead.
                $credentials[$email] = $plainPassword;

                $created++;
            } catch (\Throwable $e) {
                $errors[] = $this->rowError($rowNumber, $name, 'Registration failed: ' . $e->getMessage());
            }
        }

        return [
            'total' => count($rows),
            'created' => $created,
            'failed' => count($errors),
            'errors' => $errors,
            'credentials' => $credentials,
        ];
    }

    /** Maps every active department's abbreviation and name to its id, lower-cased. */
    private function departmentIndex(): array
    {
        $index = [];

        foreach (Department::getActive() as $department) {
            $id = (int) ($department['id'] ?? 0);

            if ($id === 0) {
                continue;
            }

            foreach ([$department['dept_abbrev'] ?? '', $department['dept_name'] ?? ''] as $label) {
                $key = $this->normalizeLabel((string) $label);

                if ($key !== '' && !isset($index[$key])) {
                    $index[$key] = $id;
                }
            }
        }

        return $index;
    }

    private function normalizeLabel(string $value): string
    {
        return strtolower(trim((string) preg_replace('/\s+/', ' ', $value)));
    }

    /** @return array{row:int,name:string,message:string} */
    private function rowError(int $row, string $name, string $message): array
    {
        return [
            'row' => $row,
            'name' => $name !== '' ? $name : "Row {$row}",
            'message' => $message,
        ];
    }
}