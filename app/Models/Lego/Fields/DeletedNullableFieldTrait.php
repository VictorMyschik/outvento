<?php

namespace App\Models\Lego\Fields;

use Carbon\Carbon;

trait DeletedNullableFieldTrait
{
    public function getDeleted()
    {
        return $this->deleted_at;
    }

    public function setDeleted(?Carbon $value): void
    {
        $this->deleted_at = $value;
    }

    public function getDeletedObject(): ?Carbon
    {
        return $this->deleted_at ? new Carbon($this->deleted_at) : null;
    }

    public function isDeleted(): bool
    {
        return (bool)$this->deleted_at;
    }
}
