<?php

namespace App\Repositories;

use Illuminate\Database\DatabaseManager;

class DatabaseRepository
{
    public function __construct(protected DatabaseManager $db) {}
}
