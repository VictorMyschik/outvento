<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Services\Telegram\TelegramService;
use App\Services\Telegram\TelegramUpdateFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class TelegramApiController
{
    public function __construct(
        private TelegramService $telegramService,
        private LoggerInterface $logger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $update = TelegramUpdateFactory::fromRequest($request);

        try {
            $this->telegramService->handle($update);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());

            if ($update->getUserId()) {
                $this->telegramService->sendRawMessage($update->getUserId(), 'Error: ' . $e->getMessage());
            }
        }

        return response()->json(['ok' => true]);
    }
}
