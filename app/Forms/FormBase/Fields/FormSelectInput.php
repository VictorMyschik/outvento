<?php

declare(strict_types=1);

namespace App\Forms\FormBase\Fields;

final class FormSelectInput extends FieldBase
{
    public array $options;

    public function getType(): string
    {
        return self::TYPE_SELECT;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
