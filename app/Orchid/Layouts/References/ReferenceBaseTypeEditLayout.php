<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class ReferenceBaseTypeEditLayout extends Rows
{
    public function fields(): array
    {
        $out[] = Input::make('reference.name_ru')->title('RU')->required()->maxlength(255);
        $out[] = Input::make('reference.name_en')->title('EN')->required()->maxlength(255);
        $out[] = Input::make('reference.name_pl')->title('PL')->required()->maxlength(255);

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
