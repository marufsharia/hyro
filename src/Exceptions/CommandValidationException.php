<?php

namespace Marufsharia\Hyro\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class CommandValidationException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * The error messages.
     */
    protected MessageBag $errors;

    /**
     * The validation rules that failed.
     */
    protected array $failedRules = [];

    /**
     * The data that failed validation.
     */
    protected array $failedData = [];

    /**
     * The field that failed validation.
     */
    protected ?string $failedField = null;

    /**
     * The validation rule that failed.
     */
    protected ?string $failedRule = null;

    /**
     * Create a new command validation exception.
     */
    public function __construct(
        string $message = '',
        ?MessageBag $errors = null,
        array $failedRules = [],
        array $failedData = [],
        ?string $failedField = null,
        ?string $failedRule = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors ?? new MessageBag();
        $this->failedRules = $failedRules;
        $this->failedData = $failedData;
        $this->failedField = $failedField;
        $this->failedRule = $failedRule;
    }

    /**
     * Create an exception from validation errors.
     */
    public static function fromErrors(
        MessageBag $errors,
        array $failedRules = [],
        array $failedData = [],
        ?string $failedField = null,
        ?string $failedRule = null
    ): self {
        $message = 'Validation failed: ' . $errors->first();

        return new self(
            $message,
            $errors,
            $failedRules,
            $failedData,
            $failedField,
            $failedRule
        );
    }

    /**
     * Create an exception from a validator instance.
     */
    public static function fromValidator($validator): self
    {
        return self::fromErrors(
            $validator->errors(),
            $validator->failed(),
            $validator->attributes(),
            null,
            null
        );
    }

    /**
     * Create an exception for a required field.
     */
    public static function required(string $field, array $data = []): self
    {
        $message = "The field '{$field}' is required.";

        $errors = new MessageBag([$field => [$message]]);

        return new self(
            $message,
            $errors,
            [$field => ['required' => []]],
            $data,
            $field,
            'required'
        );
    }

    /**
     * Create an exception for an invalid format.
     */
    public static function invalidFormat(string $field, string $expectedFormat, array $data = []): self
    {
        $message = "The field '{$field}' must be in the format: {$expectedFormat}.";

        $errors = new MessageBag([$field => [$message]]);

        return new self(
            $message,
            $errors,
            [$field => ['format' => [$expectedFormat]]],
            $data,
            $field,
            'format'
        );
    }

    /**
     * Create an exception for a duplicate value.
     */
    public static function duplicate(string $field, $value, string $model = null, array $data = []): self
    {
        $modelName = $model ? strtolower($model) : 'resource';
        $message = "The {$field} '{$value}' already exists for this {$modelName}.";

        $errors = new MessageBag([$field => [$message]]);

        return new self(
            $message,
            $errors,
            [$field => ['unique' => []]],
            $data,
            $field,
            'unique'
        );
    }

    /**
     * Create an exception for a not found resource.
     */
    public static function notFound(string $resource, $identifier, string $model = null, array $data = []): self
    {
        $modelName = $model ?: 'resource';
        $message = "{$modelName} not found: {$identifier}";

        $errors = new MessageBag([$resource => [$message]]);

        return new self(
            $message,
            $errors,
            [$resource => ['exists' => []]],
            $data,
            $resource,
            'exists'
        );
    }

    /**
     * Create an exception for insufficient permissions.
     */
    public static function insufficientPermissions(string $action, array $requiredPermissions = [], array $data = []): self
    {
        $permissions = empty($requiredPermissions)
            ? 'specific permissions'
            : implode(', ', $requiredPermissions);

        $message = "Insufficient permissions to perform '{$action}'. Required: {$permissions}";

        $errors = new MessageBag(['permissions' => [$message]]);

        return new self(
            $message,
            $errors,
            ['permissions' => ['required' => $requiredPermissions]],
            $data,
            'permissions',
            'required'
        );
    }

    /**
     * Create an exception for invalid input type.
     */
    public static function invalidType(string $field, $value, string $expectedType, array $data = []): self
    {
        $actualType = gettype($value);
        $message = "The field '{$field}' must be of type {$expectedType}, but got {$actualType}.";

        $errors = new MessageBag([$field => [$message]]);

        return new self(
            $message,
            $errors,
            [$field => ['type' => [$expectedType]]],
            $data,
            $field,
            'type'
        );
    }

    /**
     * Create an exception for out of range value.
     */
    public static function outOfRange(string $field, $value, $min = null, $max = null, array $data = []): self
    {
        $constraints = [];
        if ($min !== null) $constraints[] = "minimum: {$min}";
        if ($max !== null) $constraints[] = "maximum: {$max}";

        $constraintText = implode(', ', $constraints);
        $message = "The field '{$field}' with value '{$value}' is out of range. Must be {$constraintText}.";

        $errors = new MessageBag([$field => [$message]]);

        $rules = [];
        if ($min !== null) $rules['min'] = [$min];
        if ($max !== null) $rules['max'] = [$max];

        return new self(
            $message,
            $errors,
            [$field => $rules],
            $data,
            $field,
            'range'
        );
    }

    /**
     * Create an exception for invalid choice/selection.
     */
    public static function invalidChoice(string $field, $value, array $validChoices, array $data = []): self
    {
        $choices = implode(', ', $validChoices);
        $message = "Invalid value '{$value}' for field '{$field}'. Valid choices are: {$choices}.";

        $errors = new MessageBag([$field => [$message]]);

        return new self(
            $message,
            $errors,
            [$field => ['in' => $validChoices]],
            $data,
            $field,
            'in'
        );
    }

    /**
     * Create an exception for a production environment restriction.
     */
    public static function productionRestriction(string $command, array $data = []): self
    {
        $message = "Command '{$command}' cannot be executed in production environment without --force flag.";

        $errors = new MessageBag(['environment' => [$message]]);

        return new self(
            $message,
            $errors,
            ['environment' => ['production' => []]],
            $data,
            'environment',
            'production'
        );
    }

    /**
     * Create an exception for missing dependencies.
     */
    public static function missingDependency(string $dependency, string $requiredFor = null, array $data = []): self
    {
        $forText = $requiredFor ? " for {$requiredFor}" : '';
        $message = "Missing dependency: {$dependency}{$forText}";

        $errors = new MessageBag(['dependencies' => [$message]]);

        return new self(
            $message,
            $errors,
            ['dependencies' => ['required' => [$dependency]]],
            $data,
            'dependencies',
            'required'
        );
    }

    /**
     * Get the error messages.
     */
    public function getErrors(): MessageBag
    {
        return $this->errors;
    }

    /**
     * Get the first error message.
     */
    public function getFirstError(): ?string
    {
        return $this->errors->first();
    }

    /**
     * Get all error messages as an array.
     */
    public function getErrorMessages(): array
    {
        return $this->errors->all();
    }

    /**
     * Get error messages for a specific field.
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors->get($field);
    }

    /**
     * Get the failed validation rules.
     */
    public function getFailedRules(): array
    {
        return $this->failedRules;
    }

    /**
     * Get the data that failed validation.
     */
    public function getFailedData(): array
    {
        return $this->failedData;
    }

    /**
     * Get the field that failed validation.
     */
    public function getFailedField(): ?string
    {
        return $this->failedField;
    }

    /**
     * Get the rule that failed validation.
     */
    public function getFailedRule(): ?string
    {
        return $this->failedRule;
    }

    /**
     * Check if a specific field has errors.
     */
    public function hasError(string $field): bool
    {
        return $this->errors->has($field);
    }

    /**
     * Get the number of validation errors.
     */
    public function count(): int
    {
        return $this->errors->count();
    }

    /**
     * Convert the exception to a string representation.
     */
    public function __toString(): string
    {
        $errorCount = $this->count();
        $errorList = [];

        foreach ($this->errors->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errorList[] = "  • {$field}: {$message}";
            }
        }

        $errorString = implode("\n", $errorList);

        return <<<ERROR
        Command Validation Failed
        =========================
        {$this->getMessage()}

        Errors ({$errorCount}):
        {$errorString}

        Failed Data:
        {$this->formatFailedData()}
        ERROR;
    }

    /**
     * Format the failed data for display.
     */
    protected function formatFailedData(): string
    {
        if (empty($this->failedData)) {
            return '  (no data provided)';
        }

        $formatted = [];
        foreach ($this->failedData as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $value = 'null';
            }

            $formatted[] = "  {$key}: {$value}";
        }

        return implode("\n", $formatted);
    }

    /**
     * Create a console-friendly error message.
     */
    public function toConsoleMessage(): string
    {
        $lines = [
            '<error>VALIDATION FAILED</error>',
            '<comment>================</comment>',
            '',
        ];

        if ($this->failedField && $this->failedRule) {
            $lines[] = "<error>Field:</error> {$this->failedField}";
            $lines[] = "<error>Rule:</error> {$this->failedRule}";
            $lines[] = '';
        }

        foreach ($this->errors->toArray() as $field => $messages) {
            $lines[] = "<fg=red>✗ {$field}</>";
            foreach ($messages as $message) {
                $lines[] = "  <comment>→</comment> {$message}";
            }
            $lines[] = '';
        }

        if (!empty($this->failedRules)) {
            $lines[] = '<comment>Failed Rules:</comment>';
            foreach ($this->failedRules as $field => $rules) {
                foreach ($rules as $rule => $parameters) {
                    $paramStr = !empty($parameters) ? ' [' . implode(', ', $parameters) . ']' : '';
                    $lines[] = "  {$field}: {$rule}{$paramStr}";
                }
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Convert to array for JSON serialization.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'errors' => $this->errors->toArray(),
            'failed_rules' => $this->failedRules,
            'failed_data' => $this->failedData,
            'failed_field' => $this->failedField,
            'failed_rule' => $this->failedRule,
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTrace(),
        ];
    }

    /**
     * Convert to JSON string.
     */
    public function toJson(int $options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $options);
    }
}
