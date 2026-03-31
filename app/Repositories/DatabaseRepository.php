<?php

namespace App\Repositories;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

readonly class DatabaseRepository
{
    public function __construct(protected DatabaseManager $db) {}

    protected function newUlidId(): string
    {
        return Str::ulid()->toBase32();
    }
}
