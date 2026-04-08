<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\API\APIController;
use App\Models\Albums\Album;
use App\Services\Albums\AlbumService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\File;
use Orchid\Platform\Events\UploadedFileEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AlbumController extends APIController
{
    public function __construct(
        private readonly AlbumService $service,
    ) {}

    public function getAvatar(Request $request, Album $album)
    {
        if (!$album->avatar) {
            return $this->service->getDefaultAvatar();
        }

        return $this->service->showAvatar($album, $request->user());
    }

    public function showMedia(Request $request, int $mediaId, string $signature, int $expires): ?BinaryFileResponse
    {
        if ($this->service->getSignature($mediaId, $expires) !== $signature) {
            return null;
        }

        $path = $request->input('path');

        return Response::file(Storage::disk('albums')->path($path), [
            'Content-Type'        => mime_content_type(Storage::disk('albums')->path($path)),
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function uploadMedia(Request $request, Album $album)//: JsonResponse
    {
        $attachment = collect($request->allFiles())
            ->flatten()
            ->map(fn(UploadedFile $file) => $this->createModel($file, $request));

        $response = $attachment->count() > 1 ? $attachment : $attachment->first();

        return $this->apiResponse(data: $response, code: 201);
    }

    private function createModel(UploadedFile $file, Request $request)
    {
        $file = resolve(File::class, [
            'file'  => $file,
            'disk'  => $request->get('storage'),
            'group' => $request->get('group'),
        ]);

        if ($request->has('path')) {
            $file->path($request->input('path'));
        }

        $model = $file->load();

        $model->url = $model->url();

        event(new UploadedFileEvent($model));

        return $model;
    }
}