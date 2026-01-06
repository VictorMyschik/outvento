<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Language;

use App\Models\System\Translate;
use App\Orchid\Filters\TranslateFilter;
use App\Orchid\Layouts\FileDownloadLayout;
use App\Orchid\Layouts\Language\TranslateEditLayout;
use App\Orchid\Layouts\Language\TranslateListLayout;
use App\Orchid\Layouts\Language\UploadTranslateLayout;
use App\Services\Excel\ExcelTranslateService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslateScreen extends Screen
{
    public function __construct(
        private readonly TranslateService      $service,
        private readonly ExcelTranslateService $excelService,
        private readonly Request               $request,
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
            ModalToggle::make('Export')
                ->class('mr-btn-success')
                ->modal('download_excel')
                ->modalTitle('Export в Excel'),
            ModalToggle::make('Import')
                ->class('mr-btn-success')
                ->modal('upload_excel')
                ->modalTitle('Импорт из Excel')
                ->method('uploadTranslateExcel'),
        ];
    }

    public function layout(): iterable
    {
        return [
            TranslateFilter::displayFilterCard($this->request),
            TranslateListLayout::class,
            Layout::rows($this->getActionBottomLinkLayout()),
            Layout::modal('translate', TranslateEditLayout::class)->async('asyncGetTranslate')->size(Modal::SIZE_LG),
            Layout::modal('download_excel', FileDownloadLayout::class)->async('asyncGetDownloadUrl')->withoutApplyButton(),
            Layout::modal('upload_excel', UploadTranslateLayout::class),
        ];
    }

    public function uploadTranslateExcel(Request $request): void
    {
        $file = $request->file('file');
        if (!$file) {
            Toast::info('Файл не загружен')->delay(1000);
            return;
        }

        $this->service->importTranslateFromExcel(
            file: $file,
            headerRowNumber: (int)$request->get('header_row_number') ?: 1
        );

        Toast::info('Переводы загружены')->delay(15000);
    }

    public function purge(): void
    {
        $this->service->purge();
        Toast::info('Все переводы удалены')->delay(15000);
    }

    public function asyncGetDownloadUrl(Request $request): array
    {
        $fileName = $this->excelService->exportTranslateByFilter($this->service->getExportList());

        return [
            'fileName'     => $fileName,
            'downloadName' => ExcelTranslateService::FILE_NAME,
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

    public function getActionBottomLinkLayout(): array
    {
        return [
            Group::make([
                Button::make('Удалить все переводы')
                    ->class('mr-btn-danger')
                    ->method('purge')
                    ->confirm('Вы уверены, что хотите удалить все переводы?')
                    ->icon('trash')
            ])->autoWidth()
        ];
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
