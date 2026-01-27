<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\News\NewsGroup;
use App\Models\Other\TermsAndCondition;
use App\Orchid\Fields\CKEditor;
use App\Services\Other\TermsAndConditionsService;
use App\Services\System\Enum\Language;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class TermsAndConditionsEditScreen extends Screen
{
    protected string $name = 'Terms And Conditions';
    private ?TermsAndCondition $term = null;

    public function __construct(
        private readonly TermsAndConditionsService $service,
    ) {}

    public function description(): ?string
    {
        return View('admin.created_updated', ['value' => $this->term])->toHtml();
    }

    public function query(int $id): iterable
    {
        $this->term = TermsAndCondition::loadBy($id);

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
                ->href(route('other.terms.and.conditions')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Group::make([
                    Switcher::make('term.active')->sendTrueOrFalse()->title('Активен'),
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
            'language' => $input['language'],
            'active' => $input['active'] ?? false,
            'published_at' => $input['published_at'] ?? null,
            'text' => $input['text'],
        ];

        $this->service->saveTermsAndCondition($id, $data);
    }

    public function clone(int $id): RedirectResponse
    {
        $id = $this->service->clone($id);

        return redirect()->route('other.terms.and.conditions.edit', ['id' => $id]);
    }

    public function remove(int $id): RedirectResponse
    {
        $this->service->deleteTermsAndCondition($id);

        return redirect()->route('other.terms.and.conditions');
    }
}
