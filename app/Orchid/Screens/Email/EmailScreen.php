<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Email;

use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class EmailScreen extends Screen
{
    public function __construct(private readonly Request $request)
    {
        App::setlocale(Language::from((int)$this->request->get('locale', Language::RU->value))->getCode());
    }

    public function query(): iterable
    {
        return [];
    }

    public function name(): ?string
    {
        return 'Email';
    }

    public function description(): ?string
    {
        return 'Шаблоны писем';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $fakeData['travel_invite'] = [
            'token'       => '8cde1582275a2afe5dd535f7e31108f1',
            'name'        => 'Восхождение на Казбек',
            'travel_type' => 'Горный поход',
        ];

        $fakeData['new_news_subscription'] = [
            'unsubscribeUrl' => '#',
        ];

        return [
            Layout::rows([
                Select::make('locale')->options(Language::getSelectList())->title('Язык')->value($this->request->get('locale')),
                ViewField::make('')->view('space'),
                Button::make('Filter')->icon('filter')->name('сменить язык')->method('runFiltering')->class('mr-btn-success'),
            ]),
            Layout::tabs([
                'New Subscription' => Layout::view('mail.new_news_subscription', $fakeData['new_news_subscription']),
            ]),
        ];
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        return redirect()->route('reference.email.list', ['locale' => $request->get('locale')]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('reference.email.list');
    }
    #endregion
}
