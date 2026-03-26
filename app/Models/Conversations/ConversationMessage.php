<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ConversationMessage extends ORM
{
    public const string TABLE = 'conversation_messages';

    use AsSource;
    use Filterable;

    public $incrementing = false;

    protected $table = self::TABLE;

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}