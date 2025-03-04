<?php

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class TravelTypeEditLayout extends Rows
{
    public function fields(): array
    {
        $out[] = Input::make('travel-type.name_ru')->title('RU')->required()->maxlength(255);
        $out[] = Input::make('travel-type.name_en')->title('EN')->required()->maxlength(255);
        $out[] = Input::make('travel-type.name_pl')->title('PL')->required()->maxlength(255);

        $id = $this->query->get('travel-type')?->id;
        $path = $this->query->get('travel-type')?->getImagePath();

        $out[] = ViewField::make('')->view('hr');
        $out[] = Label::make('Image')->value('Изображение');
        $out[] = Upload::make('travel-type.image')->groups('reference')->set('oldlogo', $path)->path('tmp')->maxFiles(1);

        if ($path) {
            $out[] = Group::make([
                Button::make('Удалить изображение')
                    ->method('deleteImage')
                    ->confirm('Вы уверены, что хотите удалить изображение?')
                    ->parameters(['travelTypeId' => $id])
                    ->icon('trash')
            ]);
        }


        return $out;
    }
}
