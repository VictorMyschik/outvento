<?php

declare(strict_types=1);

namespace App\Models\ORM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ORM extends Model
{
    public function id(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public static function getTableName(): string
    {
        return (new static())->getTable();
    }

    public static function loadBy(?int $value): ?static
    {
        if ($value === null || $value === 0) {
            return null;
        }

        return static::find($value);
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function loadByOrDie(?int $value): static
    {
        if ($value === null || $value === 0) {
            throw new ModelNotFoundException();
        }

        return self::findOrFail($value);
    }
}
