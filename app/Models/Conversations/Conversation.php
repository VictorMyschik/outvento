<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Type;
use App\Services\Conversations\Enum\Visibility;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Conversation extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'conversations';

    public $incrementing = false;

    protected $table = self::TABLE;

    public function getType(): Type
    {
        return Type::from($this->type);
    }

    public function getJoinPolicy(): JoinPolicy
    {
        return JoinPolicy::from($this->join_policy);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar ? route('api.v1.conversation.avatar', ['conversation' => $this->id]) : null;
    }

    public function getAvatarBlank(): string
    {
        return $this->avatar ?? '/public/images/chat_blank.webp';
    }

    public function getVisibility(): Visibility
    {
        return Visibility::from($this->visibility);
    }
}