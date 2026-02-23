<?php

namespace Marufsharia\Hyro\Crud\Services\Crud;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Crud\Services\Crud\DTOs\Field;

/**
 * Field Parser Service
 * 
 * Parses field strings into Field DTOs.
 */
class FieldParserService
{
    /**
     * Parse fields string into collection of Field objects
     * 
     * Format: field_name:type:validation_rules
     * Example: "title:string:required,body:text:nullable"
     */
    public function parseFieldsString(?string $fieldsString): Collection
    {
        if (!$fieldsString) {
            return collect();
        }

        $fields = [];
        
        // Split by comma, but handle type definitions like decimal:10,2
        // Use regex to properly split field definitions
        preg_match_all(
            '/([a-zA-Z_][a-zA-Z0-9_]*):([^:,]+(?::\d+,\d+)?|[^:,]+)(?::([^,]+))?(?:,|$)/',
            $fieldsString,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $fieldName = $match[1];
            $fieldType = $match[2] ?? 'string';
            $rules = $match[3] ?? 'nullable';

            $fields[] = $this->parseField($fieldName, $fieldType, $rules);
        }

        return collect($fields);
    }

    /**
     * Parse single field definition
     */
    public function parseField(string $name, string $type, string $rules = 'nullable'): Field
    {
        // Extract base type and parameters (e.g., "decimal:10,2" -> "decimal")
        $baseType = explode(':', $type)[0];
        $mappedType = $this->mapFieldType($baseType);

        // Parse rules
        $rulesArray = array_map('trim', explode('|', $rules));
        
        // Detect field properties from rules
        $required = in_array('required', $rulesArray);
        $nullable = in_array('nullable', $rulesArray);
        $unique = in_array('unique', $rulesArray);

        // Auto-detect searchable, sortable, filterable
        $searchable = $this->isSearchableType($mappedType);
        $sortable = true; // Most fields are sortable
        $filterable = $this->isFilterableType($mappedType);

        return new Field(
            name: $name,
            type: $mappedType,
            label: Str::title(str_replace('_', ' ', $name)),
            rules: $rulesArray,
            default: $this->getDefaultValue($mappedType),
            options: [],
            attributes: $unique ? ['unique' => true] : [],
            searchable: $searchable,
            sortable: $sortable,
            filterable: $filterable,
            required: $required,
            nullable: $nullable,
        );
    }

    /**
     * Map database type to input type
     */
    protected function mapFieldType(string $type): string
    {
        return match (strtolower($type)) {
            'string', 'varchar' => 'text',
            'text', 'longtext' => 'textarea',
            'int', 'integer', 'bigint' => 'number',
            'decimal', 'float', 'double' => 'decimal',
            'boolean', 'bool' => 'checkbox',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'email' => 'email',
            'password' => 'password',
            'file' => 'file',
            'image' => 'image',
            'select' => 'select',
            'radio' => 'radio',
            default => 'text',
        };
    }

    /**
     * Check if field type is searchable
     */
    protected function isSearchableType(string $type): bool
    {
        return in_array($type, ['text', 'textarea', 'email', 'url']);
    }

    /**
     * Check if field type is filterable
     */
    protected function isFilterableType(string $type): bool
    {
        return in_array($type, ['select', 'radio', 'checkbox', 'date', 'datetime']);
    }

    /**
     * Get default value for field type
     */
    protected function getDefaultValue(string $type): mixed
    {
        return match ($type) {
            'checkbox' => false,
            'number', 'decimal' => 0,
            default => null,
        };
    }

    /**
     * Parse field from array
     */
    public function parseFieldArray(string $name, array $data): Field
    {
        return Field::fromArray($name, $data);
    }

    /**
     * Validate field name
     */
    public function isValidFieldName(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
    }

    /**
     * Get supported field types
     */
    public function getSupportedTypes(): array
    {
        return [
            'text' => 'Text input',
            'textarea' => 'Textarea',
            'number' => 'Number input',
            'decimal' => 'Decimal number',
            'checkbox' => 'Checkbox',
            'date' => 'Date picker',
            'datetime' => 'Datetime picker',
            'time' => 'Time picker',
            'email' => 'Email input',
            'password' => 'Password input',
            'file' => 'File upload',
            'image' => 'Image upload',
            'select' => 'Select dropdown',
            'radio' => 'Radio buttons',
        ];
    }
}
