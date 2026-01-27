<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Orchid\Filters\TermsAndConditionsFilter;
use App\Orchid\Layouts\TermsAndConditions\TermsAndConditionsCreateLayout;
use App\Orchid\Layouts\TermsAndConditions\TermsAndConditionsListLayout;
use App\Services\Other\TermsAndConditionsService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class TermsAndConditionsScreen extends Screen
{
    protected string $name = 'Terms And Conditions';

    public function __construct(
        private readonly Request                   $request,
        private readonly TermsAndConditionsService $service,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => TermsAndConditionsFilter::runQuery()->paginate(10),
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
                ->method('saveTermsAndConditions', ['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            TermsAndConditionsFilter::displayFilterCard($this->request),
            TermsAndConditionsListLayout::class,
            Layout::modal('terms_modal', TermsAndConditionsCreateLayout::class),
        ];
    }

    public function saveTermsAndConditions(Request $request, int $id): RedirectResponse
    {
        $input = Validator::make($request->all(), [
            'language' => 'required|int',
        ])->validate();

        $id = $this->service->createTermsAndCondition(Language::from((int)$input['language']));

        return redirect()->route('other.terms.and.conditions.edit', ['id' => $id]);
    }

    public function remove(int $id): void
    {
        $this->service->deleteTermsAndCondition($id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (TermsAndConditionsFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('other.terms.and.conditions', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('other.terms.and.conditions');
    }
    #endregion
}
