<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Validation;

use App\Exceptions\Validation\InputMissingException;
use App\Exceptions\Validation\MaxFileSizeException;
use App\Exceptions\Validation\PermissionDeniedException;
use App\Helpers\System\MrBaseHelper;
use App\Models\System\Settings;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

readonly class TravelImageValidation
{
    public function __construct(private ?User $user) {}

    public function validateImageShow(int $travel_id, string $imageName): array
    {
        $validator = Validator::make(['image_name' => $imageName], ['image_name' => 'required|string|max:255']);

        if ($validator->fails()) {
            throw new InputMissingException();
        }

        $name = $validator->safe()->only('image_name')['image_name'];
        /** @var TravelImage $image */
        $image = TravelImage::where('travel_id', $travel_id)->where('name', $name)->first();

        if (!$image) {
            throw new InputMissingException('Image not found');
        }

        if (!$image->canView($this->user)) {
            throw new PermissionDeniedException();
        }

        $travel = $image->getTravel();
        $path = $travel->getDirNameForImages() . DIRECTORY_SEPARATOR . $image->name;
        if (!Storage::exists($path)) {
            throw new InputMissingException('Image not found');
        }

        return ['image_id' => $image->id()];
    }

    public function validateImageList(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'travel_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            throw new InputMissingException('Travel ID is wrong');
        }

        $travel = Travel::loadByOrDie((int)$validator->safe()->only('travel_id')['travel_id']);

        if (!$travel->canView($this->user)) {
            throw new PermissionDeniedException();
        }

        return ['travel_id' => $travel->id()];
    }

    public function validateImageUpload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'image_type'  => 'required|int|min:1|max:2',
            'travel_id'   => 'required|int',
            'group'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new InputMissingException($validator->errors(), 400);
        }

        $input = $validator->safe()->only('image', 'image_type', 'travel_id', 'group', 'description');
        /** @var UploadedFile $image */
        $image = $input['image'];

        if (false === $image->getSize()) {
            throw new MaxFileSizeException('File failed to load.', 415);
        }

        // Check if user can edit this travel
        $travel = Travel::loadByOrDie((int)$input['travel_id']);

        if (!$travel->canEdit($this->user)) {
            throw new PermissionDeniedException();
        }

        $configMaxFileSize = Settings::loadMaxFileSize();
        $configMaxFileSize = (int)MrBaseHelper::strToBytes((string)$configMaxFileSize);
        $maxAllowedInINI = MrBaseHelper::getMaxUploadSize();
        $currentImageSize = MrBaseHelper::strToBytes($image->getSize());

        if ($configMaxFileSize && $currentImageSize < $configMaxFileSize) {
            return $input;
        } elseif ($configMaxFileSize === 0 && $currentImageSize < $maxAllowedInINI) {
            return $input;
        }

        throw new MaxFileSizeException('Image file size too large', 400);
    }

    public function validateImageDelete(Request $request): array
    {
        $validator = Validator::make($request->all(), ['image_id' => 'required|int|min:1']);

        if ($validator->fails()) {
            throw new InputMissingException();
        }

        $id = (int)$validator->safe()->only('image_id')['image_id'];
        $image = TravelImage::loadByOrDie($id);

        if (!$image->canEdit($this->user)) {
            throw new PermissionDeniedException();
        }

        return ['image_id' => $image->id()];
    }

    public function validateImageUpdate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'image_id'    => 'required|int|min:1',
            'image_type'  => 'nullable|int|min:1|max:2',
            'group'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new InputMissingException($validator->errors(), 400);
        }

        $input = $validator->safe()->only('image_id', 'image_type', 'group', 'description');

        $image = TravelImage::loadByOrDie($input['image_id']);

        if ($image->getTravel()->id() !== $this->user->id()) {
            throw new PermissionDeniedException();
        }

        return $input;
    }
}
