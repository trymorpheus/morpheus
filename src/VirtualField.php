<?php

namespace Morpheus;

class VirtualField
{
    private string $name;
    private string $type;
    private string $label;
    private bool $required;
    private $validator;
    private array $attributes;

    public function __construct(
        string $name,
        string $type = 'text',
        string $label = '',
        bool $required = false,
        ?callable $validator = null,
        array $attributes = []
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label ?: ucfirst(str_replace('_', ' ', $name));
        $this->required = $required;
        $this->validator = $validator;
        $this->attributes = $attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function validate($value, array $allData): bool
    {
        if ($this->required && empty($value)) {
            return false;
        }

        if ($this->validator && !empty($value)) {
            return call_user_func($this->validator, $value, $allData);
        }

        return true;
    }

    public function getErrorMessage(): string
    {
        return $this->attributes['error_message'] ?? "El campo {$this->label} no es válido";
    }
}
