<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Other;

use App\Models\Other\LegalDocument;
use App\Orchid\Fields\CKEditor;
use App\Services\Other\LegalDocuments\Enum\LegalDocumentType;
use App\Services\Other\LegalDocuments\LegalDocumentsService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class LegalDocumentEditScreen extends Screen
{
    protected string $name = 'Terms And Conditions';
    private ?LegalDocument $term = null;

    public function __construct(
        private readonly LegalDocumentsService $service,
    ) {}

    public function description(): ?string
    {
        return View('admin.created_updated', ['value' => $this->term])->toHtml();
    }

    public function query(int $id): iterable
    {
        $this->term = LegalDocument::loadBy($id);

        return ['term' => $this->term];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->method('saveTermsAndCondition')
                ->class('mr-btn-primary')
                ->parameters(['id' => $this->term?->id() ?: 0])
                ->icon('check'),
            Link::make('Назад')
                ->icon('arrow-up')
                ->class('mr-btn-primary')
                ->href(route('legal.documents.list')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Group::make([
                    Switcher::make('term.active')->sendTrueOrFalse()->title('Активен'),
                    Select::make('term.type')->required()->options(LegalDocumentType::getSelectList())->title('Тип документа'),
                    Select::make('term.language')->required()->options(Language::getSelectList())->title('Язык'),
                    DateTimer::make('term.published_at')
                        ->title('Дата публикации. Оставьте пустым, что бы опубликовать сразу')
                        ->format('Y-m-d')
                ])->fullWidth(),
            ]),
            Layout::rows([
                CKEditor::make('term.text')->title('Текст'),
            ]),
            Layout::rows([
                Group::make([
                    Button::make('Clear')->confirm('Удалить?')->class('btn btn-sm')
                        ->name('Удалить условия')
                        ->icon('trash')
                        ->method('remove')
                        ->class('mr-btn-danger')
                        ->novalidate(),
                    Button::make('Cline')->confirm('Будет создана копия')->class('btn btn-sm')
                        ->name('Clone')
                        ->icon('copy')
                        ->method('clone')
                        ->class('mr-btn-success')
                        ->novalidate(),
                ])->autoWidth()
            ])
        ];
    }

    public function saveTermsAndCondition(Request $request, int $id): void
    {
        $input = $request->all()['term'];

        $data = [
            'type' => $input['type'],
            'language' => $input['language'],
            'active' => $input['active'] ?? false,
            'published_at' => $input['published_at'] ?? null,
            'text' => $input['text'],
        ];

        $this->service->saveLegalDocument($id, $data);
    }

    public function clone(int $id): RedirectResponse
    {
        $id = $this->service->clone($id);

        return redirect()->route('other.terms.and.conditions.edit', ['id' => $id]);
    }

    public function remove(int $id): RedirectResponse
    {
        $this->service->deleteLegalDocument($id);

        return redirect()->route('other.terms.and.conditions');
    }
}
