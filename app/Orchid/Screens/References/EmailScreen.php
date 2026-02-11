<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Services\Forms\DTO\FormFeedbackDTO;
use App\Services\Notifications\NotificationService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class EmailScreen extends Screen
{
    public string $name = 'Email Templates';
    public string $description = 'Email templates preview';

    public function __construct(private readonly Request $request)
    {
        App::setlocale(Language::from((int)$this->request->get('locale', Language::EN->value))->getCode());
    }

    public function query(): iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $fakeData['reset_password'] = [
            'url'           => 'https://example.com/forgot-password?token=abcdef',
            'expireMinutes' => '20',
        ];

        $fakeData['new_news_subscription'] = [
            'unsubscribeUrl'  => '#',
            'expireMinutes'   => NotificationService::EXPIRE_MINUTES,
            'confirmationUrl' => 'https://example.com/news/confirm?token=abcdef',
        ];

        $fakeData['news_digest'] = [
            'newsDataList'   => [
                [
                    'title' => 'Новая статья о путешествиях',
                    'url'   => 'https://example.com/news/1',
                ],
                [
                    'title' => 'Лучшие маршруты 2026 года',
                    'url'   => 'https://example.com/news/2',
                ],
            ],
            'unsubscribeUrl' => '#',
        ];

        $fakeData['verify_account'] = [
            'code'          => '123456',
            'expireMinutes' => NotificationService::EXPIRE_MINUTES,
        ];

        $fakeData['feedback'] = [
            'dto' => new FormFeedbackDTO(language: Language::EN, name: 'Viktor', email: 'email@example.com', message: 'Hi', userId: 1,)
        ];

        $fakeData['verify_communication_email'] = [
            'confirmationUrl' => 'https://example.com/confirm?token=abcdef',
            'expireMinutes'   => NotificationService::EXPIRE_MINUTES,
        ];

        App::setlocale(Language::from((int)$this->request->get('locale', Language::RU->value))->getCode());

        return [
            Layout::rows([
                Select::make('locale')->options(Language::getSelectList())->title('Язык')->value($this->request->get('locale')),
                ViewField::make('')->view('space'),
                Button::make('Filter')->icon('filter')->name('сменить язык')->method('runFiltering')->class('mr-btn-success'),
            ]),

            Layout::tabs([
                'Reset Password'             => $this->getTemplate('emails.reset_password', $fakeData['reset_password']),
                'New Subscription'           => $this->getTemplate('emails.new_news_subscription', $fakeData['new_news_subscription']),
                'News Digest'                => $this->getTemplate('emails.news_digest', $fakeData['news_digest']),
                'Email verification'         => $this->getTemplate('emails.verify_email_code', $fakeData['verify_account']),
                'Feedback'                   => $this->getTemplate('emails.feedback', $fakeData['feedback']),
                'Verify Communication Email' => $this->getTemplate('emails.verify_communication_email', $fakeData['verify_communication_email']),
            ]),
        ];
    }

    private function getTemplate(string $view, array $data): Rows
    {
        return Layout::rows([
            ViewField::make('')->view('admin.raw')->value(
                View($view)->with($data)->render()
            ),
        ]);
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
