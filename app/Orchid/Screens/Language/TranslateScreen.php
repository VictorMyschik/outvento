<?php

namespace App\Orchid\Screens\Language;

use App\Models\System\Language;
use App\Models\System\Translate;
use App\Orchid\Layouts\Language\TranslateEditLayout;
use App\Orchid\Layouts\Language\TranslateListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslateScreen extends Screen
{
    public function query(Language $language): iterable
    {
        return [
            'list' => Translate::filters([])->where('language_id', $language->id())->paginate(50)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->icon('plus')
                ->modal('translate')
                ->modalTitle('Create New Translate')
                ->method('saveTranslate')
                ->asyncParameters(['id' => 0]),
            Link::make('Назад')
                ->icon('arrow-left')
                ->route('language.list'),
        ];
    }

    public function layout(): iterable
    {
        return [
            TranslateListLayout::class,
            Layout::modal('translate', TranslateEditLayout::class)->async('asyncGetTranslate'),
        ];
    }

    public function asyncGetTranslate(int $id = 0): array
    {
        return [
            'translate' => Translate::loadBy($id) ?: new Translate()
        ];
    }

    public function saveTranslate(Request $request): void
    {
        $languageID = request()->route()->parameter('language');
        $data = $request->validate([
            'translate.code'      => 'required|string|max:255',
            'translate.translate' => 'required|string|max:255',
        ])['translate'];

        $data['language_id'] = $languageID;

        try {
            $translate = Translate::loadBy((int)$request->get('id')) ?: new Translate();
            $translate->fill($data);
            $translate->save();
            Toast::info('Translate was saved');
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }

    }

    public function remove(int $id): void
    {
        try {
            Translate::loadBy($id)?->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
