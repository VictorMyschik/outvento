<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Promo;

use App\Models\Promo\Subscription;
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
            TD::make('status', 'Status')->sort(),
            TD::make('language', 'Language')->render(fn(Subscription $subscription) => $subscription->getLanguage()->getLabel())->sort(),
            TD::make('type', 'Тип')->render(fn(Subscription $subscription) => $subscription->getEvent()->getLabel())->sort(),
            TD::make('token', 'Token'),
            TD::make('created_at', 'Добавлен')->render(fn($group) => $group->created_at?->format('d.m.Y')),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Subscription $subscription) {
                    return DropDown::make()->icon('options-vertical')->list([
                        Button::make('Удалить')->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить подписку?')
                            ->method('deleteSubscription', ['subscription_id' => $subscription->id()]),
                    ]);
                }),
        ];
    }
}
