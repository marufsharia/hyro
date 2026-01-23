<?php

namespace Marufsharia\Hyro\Console\Concerns;

use Illuminate\Support\Facades\Validator;
use Marufsharia\Hyro\Exceptions\CommandValidationException;

trait Validatable
{
    /**
     * Validate input with custom rules.
     */
    protected function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            foreach ($errors as $error) {
                $this->error("Validation error: {$error}");
            }

            throw new CommandValidationException('Input validation failed');
        }

        return $validator->validated();
    }

    /**
     * Validate user identifier.
     */
    protected function validateUserIdentifier(string $identifier): void
    {
        if (empty($identifier)) {
            throw new CommandValidationException('User identifier cannot be empty');
        }

        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL) && !is_numeric($identifier)) {
            // Check if it's a valid username format
            if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,}$/', $identifier)) {
                throw new CommandValidationException(
                    'Invalid user identifier. Use email, ID, or username (min 3 chars, alphanumeric)'
                );
            }
        }
    }

    /**
     * Validate role identifier.
     */
    protected function validateRoleIdentifier(string $identifier): void
    {
        if (empty($identifier)) {
            throw new CommandValidationException('Role identifier cannot be empty');
        }

        // Check slug format
        if (!preg_match('/^[a-z0-9\-]+$/', $identifier)) {
            throw new CommandValidationException(
                'Invalid role identifier. Use lowercase alphanumeric and hyphens only'
            );
        }
    }

    /**
     * Validate privilege identifier.
     */
    protected function validatePrivilegeIdentifier(string $identifier): void
    {
        if (empty($identifier)) {
            throw new CommandValidationException('Privilege identifier cannot be empty');
        }

        // Check dot notation format with optional wildcard
        if (!preg_match('/^[a-z][a-z0-9\.\*]*$/i', $identifier)) {
            throw new CommandValidationException(
                'Invalid privilege format. Use dot notation (e.g., users.create) or wildcard (users.*)'
            );
        }
    }

    /**
     * Validate duration format.
     */
    protected function validateDuration(?string $duration): ?\DateTimeInterface
    {
        if (empty($duration)) {
            return null;
        }

        try {
            if (is_numeric($duration)) {
                // Assume minutes if numeric
                return now()->addMinutes((int) $duration);
            }

            return now()->modify($duration);
        } catch (\Exception $e) {
            throw new CommandValidationException(
                "Invalid duration format: {$duration}. Use relative formats like '1 hour', '2 days', or minutes as number"
            );
        }
    }
}
