<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\TravelImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTravelController extends Controller
{
    public function showImage(int $travel_id, string $imageName): StreamedResponse
    {
        $travel = Travel::loadByOrDie($travel_id);
        $path = $travel->getDirNameForImages() . DIRECTORY_SEPARATOR . $imageName;

        return Storage::response($path);
    }

    public function deleteImage(int $image_id): RedirectResponse
    {
        $image = TravelImage::loadByOrDie($image_id);
        $image->delete();

        return back();
    }
}
