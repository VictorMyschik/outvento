<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationUser;
use App\Repositories\Conversations\ConversationRepository;
use App\Services\Conversations\ConversationService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $userId = 1;
        $r = Conversation::join(ConversationUser::TABLE, function ($query) use ($userId) {
            $query->where(Conversation::TABLE . '.id', '=', ConversationUser::TABLE . '.conversation_id')
                ->where(ConversationUser::TABLE . '.user_id', $userId)
                ->whereNull(ConversationUser::TABLE . '.deleted_at');
        });
    }

    private static function selectRaw(): array
    {
        return [
            ConversationUser::TABLE . '.conversation_id as conversation_id',
            'users.id as user_id',
            'users.name as name',
            'users.email as email',
            'CONCAT(users.first_name, \' \', users.last_name) as full_name',
            // ConversationMessage::TABLE . '.content as content',
            // ConversationMessage::TABLE . '.created_at as created_at',
        ];
    }

    public function testDelete()
    {
        $service = app(ConversationService::class);
        $service->deleteRemovedMessages();
    }
}
