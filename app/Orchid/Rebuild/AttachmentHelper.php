<?php

declare(strict_types=1);

namespace App\Orchid\Rebuild;

use App\Models\Orchid\Attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class AttachmentHelper
{
    public static function getFile(Request $request, string $name): ?Attachment
    {
        if ($attachment = Attachment::loadBy((int)($request->get($name)['image'][0] ?? null))) {
            $path = Storage::path($attachment->getFullPath());

            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }
            $attachment->file = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);
            return $attachment;
        }

        return null;
    }
}
