<?php

namespace App\Models\Lego\Fields;

trait TypeFieldTrait
{
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $value): void
    {
        abort_unless(isset(static::getTypeList()[$value]), 400, 'Invalid type');
        $this->type = $value;
    }
}
