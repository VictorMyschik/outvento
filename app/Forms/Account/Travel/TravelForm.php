<?php

declare(strict_types=1);

namespace App\Forms\Account\Travel;

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

    protected function builderForm(array &$form, array $args): void
    {
        $travel = $this->service->getTravelById((int)$args['travel_id']);

        $form['#title'] = __('mr-t.account_form_travel_edit');

        $form['status'] = [
            '#type'          => 'select',
            '#title'         => __('mr-t.account_form_status'),
            '#default_value' => $travel ? $travel->getStatus() : TravelStatus::STATUS_DRAFT,
            '#options'       => TravelStatus::getSelectList(),
        ];

        $form['title'] = [
            '#type'  => 'textfield',
            '#title' => __('mr-t.Title'),
            '#class' => ['mr-border-radius-5'],
            '#value' => $travel?->getTitle(),
        ];

        $form['country_id'] = [
            '#type'          => 'select',
            '#title'         => __('mr-t.country'),
            '#required'      => true,
            '#default_value' => $travel ? $travel->getCountry()->id() : 0,
            '#options'       => [0 => 'не выбрано'] + $this->countryService->getSelectList()
        ];

        $form['travel_type_id'] = [
            '#type'          => 'select',
            '#title'         => __('mr-t.travel_type'),
            '#default_value' => $travel ? $travel->getTravelType()->id() : 0,
            '#options'       => TravelType::all()->pluck('name', 'id')->toArray(),
        ];

        $form['visible_kind'] = [
            '#type'          => 'select',
            '#title'         => __('mr-t.visible_kind'),
            '#default_value' => (int)$travel?->getVisibleType(),
            '#options'       => TravelVisibleType::getSelectList(),
        ];
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
