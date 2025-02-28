<?php

namespace App\Orchid\Screens\Language;

use App\Models\System\Translate;
use App\Orchid\Layouts\Language\TranslateEditLayout;
use App\Orchid\Layouts\Language\TranslateListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslateScreen extends Screen
{
    public function name(): string
    {
        return 'Translate table';
    }

    public function query(): iterable
    {
        return [
            'list' => Translate::filters([])->paginate(50)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('translate')
                ->modalTitle('Create New Translate')
                ->method('saveTranslate')
                ->asyncParameters(['id' => 0]),
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
        $data = $request->validate([
            'translate.code' => 'required|string|max:255',
            'translate.ru'   => 'nullable|string|max:255',
            'translate.en'   => 'nullable|string|max:255',
            'translate.pl'   => 'nullable|string|max:255',
        ])['translate'];

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
