<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Validation;

use App\Http\Controllers\Travel\Request\TravelDetailsRequest;
use App\Http\Controllers\Travel\Request\UpdateTravelRequest;
use App\Models\Travel\Travel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final readonly class TravelValidation
{
    public function __construct(protected ?User $user) {}

    public function validateUpdate(UpdateTravelRequest $request): array
    {
        $id = $request->get('id');

        if (!Travel::loadByOrDie($id)->canEdit($this->user)) {
            throw new PermissionDeniedException();
        }

        $data = $request->all(['title', 'description', 'status', 'country_id', 'visible_kind', 'travel_type_id']);
        $data['user_id'] = $this->user->id();
        $data['id'] = $id;

        return $data;
    }

    public function validateDelete(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int|exists:travel,id',
        ]);

        if ($validator->fails()) {
            throw new InputMissingException($validator->errors()->first());
        }

        $id = (int)$validator->safe()->only('id')['id'];

        if (!Travel::loadByOrDie($id)->canDelete($this->user)) {
            throw new PermissionDeniedException();
        }

        return ['id' => $id];
    }

    public function validateDetails(TravelDetailsRequest $request): void
    {
        if (!Travel::loadByOrDie($request->getTravelId())->canView($request->user())) {
            throw new PermissionDeniedException();
        }
    }
}
