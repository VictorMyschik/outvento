<?php

namespace App\Orchid\Layouts\Subscription;

use App\Models\Subscription\Subscription;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SubscriptionListLayout extends Table
{
    public $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'id')->sort(),
            TD::make('email', 'Email')->sort(),
            TD::make('language', 'Language')->render(fn(Subscription $subscription) => $subscription->getLanguage()->getLabel())->sort(),
            TD::make('type', 'Тип')->render(fn(Subscription $subscription) => $subscription->getType()->getLabel())->sort(),
            TD::make('token', 'Token'),
            TD::make('created_at', 'Добавлен')->render(fn($group) => $group->created_at?->format('d.m.Y')),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Subscription $subscription) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('Изменить')
                            ->icon('pencil')
                            ->modal('subscription_modal')
                            ->modalTitle('Подписка')
                            ->method('saveSubscription')
                            ->asyncParameters(['subscription_id' => $subscription->id()]),

                        Button::make('Удалить')->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить подписку?')
                            ->method('deleteSubscription', ['subscription_id' => $subscription->id()]),
                    ]);
                }),
        ];
    }
}
