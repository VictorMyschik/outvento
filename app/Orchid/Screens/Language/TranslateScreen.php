<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Language;

use App\Models\System\Translate;
use App\Orchid\Filters\TranslateFilter;
use App\Orchid\Layouts\Language\TranslateEditLayout;
use App\Orchid\Layouts\Language\TranslateListLayout;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslateScreen extends Screen
{
    public function __construct(
        private readonly TranslateService $service,
        private readonly Request          $request,
    ) {}

    public function name(): string
    {
        return 'Translate table';
    }

    public function query(): iterable
    {
        return [
            'list' => TranslateFilter::runQuery()->paginate(50, [Translate::getTableName() . '.*']),
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
            TranslateFilter::displayFilterCard($this->request),
            TranslateListLayout::class,
            Layout::modal('translate', TranslateEditLayout::class)->async('asyncGetTranslate'),
        ];
    }

    public function asyncGetTranslate(int $id = 0): array
    {
        $groupOptions = $this->service->getGroupsForTranslate($id);

        $options = [];
        foreach ($groupOptions as $groupOption) {
            $options[$groupOption] = TranslateGroupEnum::from($groupOption)->getLabel();
        }

        return [
            'translate'       => Translate::loadBy($id),
            'groups_selected' => $options,
        ];
    }

    public function saveTranslate(Request $request, int $id): void
    {
        $data = $request->validate([
            'translate.code' => 'required|string|max:255',
            'translate.ru'   => 'nullable|string|max:1000',
            'translate.en'   => 'nullable|string|max:1000',
            'translate.pl'   => 'nullable|string|max:1000',
        ])['translate'];

        $groups = $request->input('groups_selected') ?? [];

        $this->service->saveTranslate($id, $data, $groups);
    }

    public function remove(int $id): void
    {
        try {
            Translate::loadBy($id)?->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(TranslateFilter::FIELDS);

        $list = [];
        foreach (TranslateFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('language.translate.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('language.translate.list');
    }
    #endregion
}
