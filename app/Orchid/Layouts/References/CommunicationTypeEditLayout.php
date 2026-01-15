<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class CommunicationTypeEditLayout extends Rows
{
    public function fields(): array
    {
        $out[] = Input::make('communication-type.name_ru')->title('RU')->required()->maxlength(255);
        $out[] = Input::make('communication-type.name_en')->title('EN')->required()->maxlength(255);
        $out[] = Input::make('communication-type.name_pl')->title('PL')->required()->maxlength(255);

        $id = $this->query->get('communication-type')?->id;
        $path = $this->query->get('communication-type')?->getImagePath();

        $out[] = ViewField::make('')->view('hr');
        $out[] = Label::make('Image')->value('Изображение');
        $out[] = Upload::make('communication-type.image')->groups('reference')->set('oldlogo',  Storage::url($path))->path('tmp')->maxFiles(1);

        if ($path) {
            $out[] = Group::make([
                Button::make('Удалить изображение')
                    ->method('deleteImage')
                    ->confirm('Вы уверены, что хотите удалить изображение?')
                    ->parameters(['communicationTypeId' => $id])
                    ->icon('trash')
            ]);
        }


        return $out;
    }
}
