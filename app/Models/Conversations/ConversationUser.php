<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationUser extends ORM
{
    public const string TABLE = 'conversation_users';

    use AsSource;
    use Filterable;

    protected $table = self::TABLE;
}