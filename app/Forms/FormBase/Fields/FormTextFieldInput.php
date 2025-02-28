<?php

declare(strict_types=1);

namespace App\Forms\FormBase\Fields;

final class FormTextFieldInput extends FieldBase
{
    public ?string $placeholder = null;

    public function getType(): string
    {
        return self::TYPE_TEXT;
    }

    public function setPlaceholder(?string $value): self
    {
        $this->placeholder = $value;

        return $this;
    }
}
