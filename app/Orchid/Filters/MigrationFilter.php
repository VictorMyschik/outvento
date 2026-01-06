<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\Migration;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class MigrationFilter extends Filter
{
    public static function runQuery(): mixed
    {
        return Migration::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }
}