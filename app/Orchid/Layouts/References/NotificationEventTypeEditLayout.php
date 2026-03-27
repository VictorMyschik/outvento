<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Notification\NotificationEventType;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class NotificationEventTypeEditLayout extends Rows
{
    public function fields(): array
    {
        $out[] = Input::make('reference.code')->title('Code')->required()->maxlength(255);
        $out[] = Relation::make('reference.category')
            ->allowAdd(true)
            ->fromModel(NotificationEventType::class, 'category', 'category')
            ->required()
            ->title('Category');
        $out[] = Input::make('reference.title')->title('Title')->required()->maxlength(255);
        $out[] = Input::make('reference.description')->title('Description')->maxlength(255);

        $out[] = Select::make('reference.roles')
            ->options($this->query->get('roleOptions', []))
            ->value($this->query->get('roleValues', []))
            ->multiple()
            ->required()
            ->title('Roles');

        $id = $this->query->get('reference')?->id;
        $path = $this->query->get('reference')?->getImageUrl();

        $out[] = ViewField::make('')->view('hr');
        $out[] = Label::make('Image')->value('Изображение');
        $out[] = Upload::make('reference.image')->groups('reference')->set('oldlogo', $path)->path('tmp')->maxFiles(1);

        if ($path) {
            $out[] = Group::make([
                Button::make('Удалить изображение')
                    ->method('deleteImage')
                    ->confirm('Вы уверены, что хотите удалить изображение?')
                    ->parameters(['referenceTypeId' => $id])
                    ->icon('trash')
            ]);
        }


        return $out;
    }
}
