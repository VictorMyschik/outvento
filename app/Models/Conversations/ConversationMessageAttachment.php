<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationMessageAttachment extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'conversation_message_attachments';

    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'id',
        'name',
        'size',
        'user_id',
        'created_at',
    ];
}