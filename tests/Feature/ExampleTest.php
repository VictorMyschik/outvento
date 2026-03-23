<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageUserState;
use App\Models\Conversations\ConversationUser;
use App\Services\Conversations\ConversationService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $r = ConversationMessage::join(ConversationMessageUserState::TABLE, ConversationMessageUserState::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id')
            ->where(ConversationMessageUserState::TABLE . '.updated_at', '=', null)
            ->where('conversation_id', 1)
            ->orderByDesc('created_at')
            ->get()->all();
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
