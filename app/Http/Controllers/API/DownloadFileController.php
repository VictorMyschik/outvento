<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DownloadFileController extends APIController
{
    // Скачивание временных файлов
    public function download(Request $request): BinaryFileResponse
    {
        if (!Storage::disk($request->get('disk', 'public'))->exists($request->get('file'))) {
            return throw new NotFoundHttpException('File not found');
        }

        return Response::download(Storage::disk($request->get('disk', 'public'))->path($request->get('file')), $request->get('name'))->deleteFileAfterSend();
    }
}
