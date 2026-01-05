<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use App\Services\Constructor\ConstructorService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockEditLayout extends Rows
{
    public function __construct(private readonly ConstructorService $service) {}

    public function fields(): array
    {
        $logoPath = null;
        $image = null;
        $id = (int)$this->query->get('block.id');

        if ($id) {
            $logo = $this->service->getBlockIcon($id);
            if ($logo) {
                $logoPath = $logo->getUrl();

                $image = Button::make('Удалить изображение')
                    ->method('deleteBlockIcon')
                    ->confirm('Вы уверены, что хотите удалить изображение?')
                    ->parameters(['block_id' => $id])
                    ->icon('trash');
            }
        }

        $out[] = Input::make('block.title')->type('text')->max(255)->required()->title('Заголовок');

        if ($this->query->get('need_icon')) {
            $out[] = ViewField::make('')->view('hr');
            $out[] = Label::make('Image')->value('Иконка блока');
            $out[] = Upload::make('block.icon')->groups('photo')->set('oldlogo', $logoPath)->maxFiles(1)->path('tmp');
            $image && $out[] = Group::make([$image]);

            $out[] = ViewField::make('')->view('hr');
        }

        $out[] = Input::make('block.sort')->type('number')->min(0)->max(999)->title('Сортировка блока');

        return $out;
    }
}