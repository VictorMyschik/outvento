<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Forms;

use App\Orchid\Filters\Forms\FromFilter;
use App\Orchid\Layouts\Forms\FormCommentEditLayout;
use App\Orchid\Layouts\Forms\FormRequestListLayout;
use App\Orchid\Layouts\Lego\InfoRawModalLayout;
use App\Services\Forms\FormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FormsScreen extends Screen
{
    public function __construct(private readonly FormService $service) {}

    public string $name = 'Заявки с форм';
    public string $description = 'Просмотр и управление заявками, отправленными через формы на сайте';

    public function query(): iterable
    {
        return [
            'list' => FromFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Отметить все как прочитанные')
                ->class('mr-btn-primary')
                ->confirm('Уверены? Отметить все заявки как прочитанные?')
                ->method('runAllAsRead')
                ->icon('check'),
        ];
    }

    public function layout(): iterable
    {
        return [
            FromFilter::displayFilterCard(request()),
            FormRequestListLayout::class,
            Layout::rows($this->getActionBottomLinkLayout()),
            Layout::modal('form_modal', FormCommentEditLayout::class)->async('asyncGetFormComment')->size(Modal::SIZE_LG),
            Layout::modal('form_details_modal', InfoRawModalLayout::class)->async('asyncGetDetailsForm')->withoutApplyButton()->size(Modal::SIZE_LG),
        ];
    }

    public function getActionBottomLinkLayout(): array
    {
        return [
            Group::make([
                Button::make('Удалить все сообщения')
                    ->class('mr-btn-danger')
                    ->confirm('Уверены? Удалить все заявки?')
                    ->method('deleteAllRequests')
                    ->icon('trash'),
            ])->autoWidth()
        ];
    }

    public function asyncGetDetailsForm(int $id = 0): array
    {
        $form = $this->service->getFormById($id);
        if (!$form) {
            return ['view' => null];
        }

        return ['view' => ViewField::make('')->view('admin.form.' . strtolower($form->getType()->name))->value($form)->render()];
    }

    public function asyncGetFormComment(int $id): array
    {
        return ['form' => $this->service->getFormById($id)];
    }

    public function deleteForm(int $id): void
    {
        $this->service->deleteForm($id);
    }

    public function saveFormComment(Request $request, int $id): void
    {
        $input = Validator::make($request->all(), [
            'form.active'      => 'nullable|boolean',
            'form.description' => 'nullable|string'
        ])->validate()['form'];

        $this->service->saveFormComment($id, $input);
    }

    public function deleteAllRequests(): void
    {
        $this->service->deleteAllRequests();
    }

    public function runAllAsRead(): void
    {
        $this->service->runAllAsRead();

        Toast::message('Все заявки отмечены как прочитанные')->delay(1500);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (FromFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('forms.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('forms.list');
    }
    #endregion
}