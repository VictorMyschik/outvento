<?php

namespace App\Repositories;

use Illuminate\Database\DatabaseManager;

readonly class DatabaseRepository
{
    public function __construct(protected DatabaseManager $db) {}
}
