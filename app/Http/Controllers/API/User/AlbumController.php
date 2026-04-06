<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\API\APIController;
use App\Models\Albums\Album;
use App\Services\Albums\AlbumService;
use Illuminate\Http\Request;

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
}