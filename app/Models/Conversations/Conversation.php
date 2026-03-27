<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Conversation extends ORM
{
    public const string TABLE = 'conversations';

    use AsSource;
    use Filterable;

    public $incrementing = false;

    protected $table = 'conversations';
}