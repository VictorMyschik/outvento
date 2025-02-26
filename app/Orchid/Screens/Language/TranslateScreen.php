<?php

namespace App\Orchid\Screens\Language;

use App\Models\System\Translate;
use App\Orchid\Layouts\Language\TranslateEditLayout;
use App\Orchid\Layouts\Language\TranslateListLayout;
use App\Services\System\Enum\Language;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslateScreen extends Screen
{
    public function name(): string
    {
        return 'Translate for Language ' . Language::tryFrom((int)request()->route()->parameter('language'))?->getLabel();
    }

    public function query(int $language): iterable
    {
        return [
            'list' => Translate::filters([])->where('language_id', $language)->paginate(50)
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

    public function saveTranslate(Request $request, int $language): void
    {
        $data = $request->validate([
            'translate.code'      => 'required|string|max:255',
            'translate.translate' => 'required|string|max:255',
        ])['translate'];

        $data['language'] = $language;

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
