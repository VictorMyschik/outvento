<?php

namespace App\Models\Lego\Fields;

trait KindFieldTrait
{
    public function getKind(): int
    {
        return $this->kind;
    }

    public function setKind(int $value): void
    {
        abort_if(!isset(self::getKindList()[$value]), 500, 'Unknown kind');

        $this->kind = $value;
    }

    public function getKindName(): string
    {
        return self::getKindList()[$this->getKind()];
    }
}
