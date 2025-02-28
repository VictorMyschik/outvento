<?php

declare(strict_types=1);

namespace App\Forms\Account\Travel;

use App\Forms\FormBase\Fields\FormSelectInput;
use App\Forms\FormBase\Fields\FormTextFieldInput;
use App\Forms\FormBase\FormBase;
use App\Models\Travel\TravelType;
use App\Services\References\CountryService;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use App\Services\Travel\TravelService;

class TravelForm extends FormBase
{
    public string $size = self::SIZE_50;

    public function __construct(
        private readonly TravelService  $service,
        private readonly CountryService $countryService
    ) {}

    protected function builderForm(array $args): array
    {
        $travel = $this->service->getTravelById((int)$args['travel_id']);

        $this->title = __('mr-t.account_form_travel_create');
        if ($travel) {
            $this->title = __('mr-t.account_form_travel_edit');
        }

        $inputs[] = FormSelectInput::make('status')
            ->options(TravelStatus::getSelectList())
            ->title(__('mr-t.account_form_status'))
            ->value($travel?->getStatus());

        $inputs[] = FormTextFieldInput::make('title')
            ->title(__('mr-t.title'))
            ->value($travel?->getTitle());

        $inputs[] = FormSelectInput::make('country_id')
            ->options([0 => 'не выбрано'] + $this->countryService->getSelectList($this->getLanguage()))
            ->title(__('mr-t.country'))
            ->value($travel?->getCountry()->id());

        $inputs[] = FormSelectInput::make('travel_type_id')
            ->options(TravelType::all()->pluck('name', 'id')->toArray())
            ->title(__('mr-t.travel_type'))
            ->value($travel?->getTravelType()->id());

        $inputs[] = FormSelectInput::make('visible_type')
            ->options(TravelVisibleType::getSelectList())
            ->title(__('mr-t.visible_type'))
            ->value($travel?->getVisibleType()->value);

        return $inputs;
    }

    protected function validateForm(array $routeParameters): void
    {
        if (!$this->v['title']) {
            $this->errors['title'] = 'Наименование не указано';
        }

        if (!$this->v['country_id']) {
            $this->errors['country_id'] = 'Страна не указана';
        }
    }

    protected function submitForm(array $routeParameters): void
    {
        $this->v['user_id'] = auth()->id();

        if ((int)$routeParameters['travel_id'] > 0) {
            $this->service->updateTravel((int)$routeParameters['travel_id'], $this->v);
        } else {
            $this->service->createTravel($this->v);
        }
    }
}
