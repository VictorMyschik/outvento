<?php

namespace App\Http\Controllers\Travel;

use App\Classes\Travel\Image\ImageClass;
use App\Classes\Validation\TravelImageValidation;
use App\Http\Controllers\Controller;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelImageController extends Controller
{
    public function __construct(private readonly ImageClass $imageClass, private readonly TravelImageValidation $validationClass)
    {
        $this->middleware('auth.jwt', ['except' => ['getList', 'showImage', 'showImageAdmin']]);
    }

    public function showImage(int $travel_id, string $imageName): StreamedResponse
    {
        $input = $this->validationClass->validateImageShow($travel_id, $imageName);

        $image = TravelImage::loadByOrDie($input['image_id']);
        $travel = $image->getTravel();
        $path = $travel->getDirNameForImages() . DIRECTORY_SEPARATOR . $image->name;

        return Storage::response($path);
    }

    public function getList(Request $request): JsonResponse
    {
        $input = $this->validationClass->validateImageList($request);

        $travel = Travel::loadByOrDie((int)$input['travel_id']);

        $list = $travel->getFullImagesList();

        $out = [];

        foreach ($list as $image) {
            $out[] = $this->imageClass->getTravelImageData($image);
        }

        return $this->successResult($out);
    }

    public function imageUpload(Request $request): JsonResponse
    {
        $input = $this->validationClass->validateImageUpload($request);

        $result = $this->imageClass->uploadImage(Travel::loadByOrDie((int)$input['travel_id']), $input['image'], $input);

        return $this->successResult($result);
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $input = $this->validationClass->validateImageDelete($request);

        $image = TravelImage::loadByOrDie((int)$input['image_id']);
        $image->delete();

        return $this->successResult();
    }

    public function updateImage(Request $request): JsonResponse
    {
        $input = $this->validationClass->validateImageUpdate($request);

        $image = TravelImage::loadByOrDie($input['image_id']);

        $this->imageClass->setImageProperties($image, $input);

        $image->save();

        return $this->successResult();
    }
}
