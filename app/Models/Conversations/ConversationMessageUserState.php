<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;

class ConversationMessageUserState extends ORM
{
    public const string TABLE = 'conversation_message_user_state';

    protected $table = self::TABLE;

    public $timestamps = false;

    public $fillable = [
        'message_id',
        'user_id',
        'deleted_at',
    ];
}