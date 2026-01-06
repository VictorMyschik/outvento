<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;

class ImageUpload
{
    public static function make(?int $id, ?string $path): array
    {
        $out[] = ViewField::make('')->view('hr');
        $out[] = Label::make('Image')->value('Изображение');
        $out[] = Upload::make('promo.logo')->groups('photo')->set('oldlogo', $path)->maxFiles(1)->path('tmp');

        if ($path) {
            $image = Button::make('Удалить изображение')
                ->method('deletePromoLogo')
                ->confirm('Вы уверены, что хотите удалить изображение?')
                ->parameters(['travelTypeId' => $id])
                ->icon('trash');
            $out[] = Group::make([$image]);
        }

        return $out;
    }
}
