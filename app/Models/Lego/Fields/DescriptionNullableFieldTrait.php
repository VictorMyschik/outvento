<?php

namespace App\Models\Lego\Fields;

trait DescriptionNullableFieldTrait
{
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $value): void
    {
        // Пустую строку не храним - сразу null
        if (empty(trim((string)$value)))
            $value = null;

        $this->description = $value;
    }
}