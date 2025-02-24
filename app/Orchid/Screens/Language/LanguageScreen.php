<?php

namespace App\Orchid\Screens\Language;

use App\Models\System\Language;
use App\Orchid\Layouts\Language\LanguageEditLayout;
use App\Orchid\Layouts\Language\LanguageListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LanguageScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'list' => Language::filters([])->paginate(20)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->type(Color::PRIMARY())
                ->icon('plus')
                ->modal('language')
                ->modalTitle('Create New Language')
                ->method('saveLanguage')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            LanguageListLayout::class,
            Layout::modal('language', LanguageEditLayout::class)->async('asyncGetLanguage'),
        ];
    }

    public function asyncGetLanguage(int $id = 0): array
    {
        return [
            'language' => Language::loadBy($id) ?: new Language()
        ];
    }

    public function saveLanguage(Request $request): void
    {
        $data = $request->validate([
            'language.active' => 'required|boolean',
            'language.code'   => 'required|string|max:2',
            'language.name'   => 'required|string|max:50',
        ])['language'];

        Language::updateOrCreate(
            ['id' => (int)$request->get('id')],
            $data
        );

        Toast::info('Language was saved');
    }

    public function remove(int $id): void
    {
        try {
            Language::loadBy($id)?->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
