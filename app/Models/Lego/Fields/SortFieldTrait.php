<?php

namespace App\Models\Lego\Fields;

trait SortFieldTrait
{
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $value): void
    {
        $this->sort = $value;
    }
}