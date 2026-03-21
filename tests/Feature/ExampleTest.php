<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversations\ConversationMessage;
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
        $userId = 1;

        $conversationIds = ConversationUser::where('user_id', $userId)
            ->whereNull(ConversationUser::TABLE . '.deleted_at')
            ->pluck('conversation_id')->toArray();

        $r =  ConversationUser::query()->whereIn(ConversationUser::TABLE . '.conversation_id', $conversationIds)
            ->whereNull(ConversationUser::TABLE . '.deleted_at')
            ->groupBy(ConversationUser::TABLE . '.conversation_id')
            ->havingRaw("count(" . ConversationUser::TABLE . ".conversation_id) = 1")
            ->value('conversation_id');

        $sql = $r->toSql();

        $result = $r->get()->toArray();
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
