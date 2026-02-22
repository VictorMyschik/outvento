<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel;

use App\Http\Controllers\API\APIController;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class TravelController extends ApiController
{
    public function __construct(
        //private readonly TravelService $travelService,
    ) {}

    public function getTravelAvatar(Travel $travel, TravelMedia $media): Response
    {
        if ($media->travel_id !== $travel->id) {
            abort(404);
        }

        return response()->file(Storage::disk('travels')->path($media->path));
    }
}
