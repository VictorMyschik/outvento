<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationPinnedMessage extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'conversation_pinned_messages';
    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'pinned_at',
        'user_name',
    ];
}