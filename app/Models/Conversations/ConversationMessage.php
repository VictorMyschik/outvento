<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationMessage extends ORM
{
    public const string TABLE = 'conversation_messages';

    use AsSource;
    use Filterable;

    public const null UPDATED_AT = null;

    public $incrementing = false;

    protected $table = self::TABLE;

    public $casts = [
        'created_at' => 'datetime',
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];
}