<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Other;

use App\Orchid\Filters\LegalDocumentsFilter;
use App\Orchid\Layouts\LegalDocuments\LegalDocumentCreateLayout;
use App\Orchid\Layouts\LegalDocuments\LegalDocumentsListLayout;
use App\Services\Other\LegalDocuments\Enum\LegalDocumentType;
use App\Services\Other\LegalDocuments\LegalDocumentsService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class LegalDocumentsScreen extends Screen
{
    protected string $name = 'Legal Documents';

    public function __construct(
        private readonly Request               $request,
        private readonly LegalDocumentsService $service,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => LegalDocumentsFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('добавить')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('terms_modal')
                ->modalTitle('Создать новый')
                ->method('saveLegalDocuments', ['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            LegalDocumentsFilter::displayFilterCard($this->request),
            LegalDocumentsListLayout::class,
            Layout::modal('terms_modal', LegalDocumentCreateLayout::class),
        ];
    }

    public function saveLegalDocuments(Request $request, int $id): RedirectResponse
    {
        $input = Validator::make($request->all(), [
            'language' => 'required|int',
            'type'     => 'nullable|string',
        ])->validate();

        $id = $this->service->createLegalDocument(LegalDocumentType::from((string)($input['type'])), Language::from((int)$input['language']));

        return redirect()->route('legal.documents.edit', ['id' => $id]);
    }

    public function remove(int $id): void
    {
        $this->service->deleteLegalDocument($id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (LegalDocumentsFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('legal.documents.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('legal.documents.list');
    }
    #endregion
}
