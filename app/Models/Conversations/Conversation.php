<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Conversation extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'conversations';

    public $incrementing = false;

    protected $table = self::TABLE;
}