<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationMessageLink extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'conversation_message_links';

    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'id',
        'message_id',
        'conversation_id',
        'user_id',
        'url',
        'host',
        'created_at',
    ];
}
