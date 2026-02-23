<?php

namespace Marufsharia\Hyro\Services\Crud\DTOs;

/**
 * Field Data Transfer Object
 * 
 * Represents a single field in a CRUD resource.
 */
class Field
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $label = null,
        public readonly array $rules = [],
        public readonly mixed $default = null,
        public readonly array $options = [],
        public readonly array $attributes = [],
        public readonly bool $searchable = false,
        public readonly bool $sortable = false,
        public readonly bool $filterable = false,
        public readonly bool $required = false,
        public readonly bool $nullable = false,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            type: $data['type'] ?? 'string',
            label: $data['label'] ?? str($name)->title()->toString(),
            rules: $data['rules'] ?? [],
            default: $data['default'] ?? null,
            options: $data['options'] ?? [],
            attributes: $data['attributes'] ?? [],
            searchable: $data['searchable'] ?? false,
            sortable: $data['sortable'] ?? false,
            filterable: $data['filterable'] ?? false,
            required: $data['required'] ?? false,
            nullable: $data['nullable'] ?? false,
        );
    }

    /**
     * Get database column type
     */
    public function getDatabaseType(): string
    {
        return match ($this->type) {
            'string', 'text', 'email', 'url', 'tel', 'password' => 'string',
            'textarea', 'wysiwyg', 'markdown' => 'text',
            'integer', 'number' => 'integer',
            'decimal', 'float', 'money' => 'decimal',
            'boolean', 'checkbox', 'switch' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'timestamp',
            'time' => 'time',
            'json' => 'json',
            'file', 'image' => 'string',
            'select', 'radio' => 'string',
            default => 'string',
        };
    }

    /**
     * Get PHP cast type
     */
    public function getCastType(): ?string
    {
        return match ($this->type) {
            'boolean', 'checkbox', 'switch' => 'boolean',
            'integer', 'number' => 'integer',
            'decimal', 'float', 'money' => 'decimal:2',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'json' => 'array',
            default => null,
        };
    }

    /**
     * Get validation rules as string
     */
    public function getValidationRules(): string
    {
        $rules = $this->rules;

        if ($this->required) {
            $rules[] = 'required';
        }

        if ($this->nullable) {
            $rules[] = 'nullable';
        }

        // Add type-specific rules
        $rules = array_merge($rules, $this->getTypeSpecificRules());

        return implode('|', array_unique($rules));
    }

    /**
     * Get type-specific validation rules
     */
    protected function getTypeSpecificRules(): array
    {
        return match ($this->type) {
            'email' => ['email'],
            'url' => ['url'],
            'integer', 'number' => ['integer'],
            'decimal', 'float', 'money' => ['numeric'],
            'boolean', 'checkbox', 'switch' => ['boolean'],
            'date' => ['date'],
            'datetime', 'timestamp' => ['date'],
            'image' => ['image', 'max:2048'],
            'file' => ['file', 'max:10240'],
            default => [],
        };
    }

    /**
     * Check if field is a file upload
     */
    public function isFileUpload(): bool
    {
        return in_array($this->type, ['file', 'image']);
    }

    /**
     * Check if field is a text field
     */
    public function isTextField(): bool
    {
        return in_array($this->type, ['string', 'text', 'email', 'url', 'tel', 'password']);
    }

    /**
     * Check if field is a number field
     */
    public function isNumberField(): bool
    {
        return in_array($this->type, ['integer', 'number', 'decimal', 'float', 'money']);
    }

    /**
     * Check if field is a date field
     */
    public function isDateField(): bool
    {
        return in_array($this->type, ['date', 'datetime', 'timestamp', 'time']);
    }

    /**
     * Get HTML input type
     */
    public function getHtmlInputType(): string
    {
        return match ($this->type) {
            'email' => 'email',
            'url' => 'url',
            'tel' => 'tel',
            'password' => 'password',
            'number', 'integer' => 'number',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'time' => 'time',
            default => 'text',
        };
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'rules' => $this->rules,
            'default' => $this->default,
            'options' => $this->options,
            'attributes' => $this->attributes,
            'searchable' => $this->searchable,
            'sortable' => $this->sortable,
            'filterable' => $this->filterable,
            'required' => $this->required,
            'nullable' => $this->nullable,
        ];
    }
}
