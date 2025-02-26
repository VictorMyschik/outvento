<?php

namespace App\Orchid\Screens\Language;

use App\Models\System\Translate;
use App\Orchid\Layouts\Language\LanguageEditLayout;
use App\Orchid\Layouts\Language\LanguageListLayout;
use App\Services\System\Enum\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LanguageScreen extends Screen
{
    public string $name = 'Language';

    public function query(): iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $out = [];
        $rows = [];
        foreach (Language::getSelectList() as $key => $language) {
            $item = [
                Group::make([
                   Link::make($language)
                       ->target('_blank')
                        ->route('language.translate.list', ['language' => $key])
                        ->icon('bs.pencil-square'),

                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Are you sure you want to delete the Language?'))
                        ->method('remove', ['id' => 0]),
                ])->autoWidth(),
            ];

            $out[] = Layout::rows($item);
        }

        return $out;
    }

    public function remove(int $id): void
    {
        try {
            Translate::where('language', $id)->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
