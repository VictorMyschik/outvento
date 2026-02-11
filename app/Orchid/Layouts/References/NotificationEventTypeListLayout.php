<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Notification\NotificationEventType;
use App\Models\Reference\ReferenceBaseInterface;
use App\Services\References\ReferenceRepositoryInterface;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NotificationEventTypeListLayout extends Table
{
    public $target = 'list';

    public function __construct(private ReferenceRepositoryInterface $repository) {}

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('#', 'Image')->render(function (ReferenceBaseInterface $notificationEventType) {
                return View('admin.image')->with(['path' => $notificationEventType->getImageUrl()]);
            }),
            TD::make('code', 'Code')->sort(),
            TD::make('category', 'Category')->sort(),
            TD::make('title', 'Title')->sort(),
            TD::make('description', 'Description')->sort(),
            TD::make('', 'Roles')->render(function (NotificationEventType $notificationEventType) {
                return implode(', ', $this->repository->getRolesByModel($notificationEventType));
            })->sort(),
            TD::make('created_at', 'Created')->sort()->render(fn(NotificationEventType $client) => $client->created_at),
            TD::make('updated_at', 'Updated')->sort()->render(fn(NotificationEventType $client) => $client->updated_at),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(ReferenceBaseInterface $notificationEventType) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('reference')
                            ->modalTitle('Edit type id ' . $notificationEventType->id())
                            ->method('saveReferenceType')
                            ->asyncParameters(['id' => $notificationEventType->id()]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the communication type?'))
                            ->method('remove', ['id' => $notificationEventType->id()]),
                    ])),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
