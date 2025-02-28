<?php

declare(strict_types=1);

namespace App\Forms\FormBase\Fields;

class FieldBase
{
    public const string TYPE_TEXT = 'textfield';
    public const string TYPE_SELECT = 'select';

    public string $name;
    public bool $autofocus = false;
    public ?string $title;
    public mixed $value = null;
    public array $classes = [];

    public static function make(string $name): static
    {
        $object = new static();

        $object->name = $name;

        return $object;
    }

    public function setAutofocus(bool $autofocus): self
    {
        $this->autofocus = $autofocus;

        return $this;
    }


    public function value(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function title(?string $value): self
    {
        $this->title = $value;

        return $this;
    }

    public function setClasses(array $classes): self
    {
        $this->classes = $classes;

        return $this;
    }
}
