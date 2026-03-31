<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\API\APIController;
use App\Models\Conversations\ConversationMessageAttachment;
use App\Services\Conversations\ConversationFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConversationController extends APIController
{
    public function __construct(
        private readonly ConversationFileService $service,
    ) {}

    public function getFile(Request $request, int $conversation_id, int $attachment_id): BinaryFileResponse
    {
        $attachment = ConversationMessageAttachment::find($attachment_id);

        return Response::download(Storage::disk('conversations')->path($attachment->path), $attachment->name);
    }

    public function getAdminFile(Request $request, int $conversationId, string $hash): BinaryFileResponse
    {
        if (!$request->user()) {
            throw new \RuntimeException('Unauthorized', 401);
        }

        $attachment = $this->service->findExistsAttachment($conversationId, $hash);

        return Response::download(Storage::disk('conversations')->path($attachment->path), $attachment->name);
    }

    public function getAdminFilesZip(Request $request, int $conversationId, string $messageId): StreamedResponse
    {
        if (!$request->user()) {
            throw new \RuntimeException('Unauthorized', 401);
        }

        $attachments = $this->service->getMessageFiles($messageId);

        if (empty($attachments)) {
            throw new NotFoundHttpException('No attachments found for this message');
        }

        return $this->service->getMessageFileArchived($messageId, $attachments);
    }

    public function showMedia(Request $request, int $conversationId, int $fileId)
    {
        $file = ConversationMessageAttachment::loadBy($fileId);

        return Response::file(Storage::disk('conversations')->path($file->path), [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->name . '"',
        ]);
    }
}