<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Promo;

use App\Helpers\RegexHelper;
use App\Orchid\Filters\Promo\SubscriptionFilter;
use App\Orchid\Layouts\Promo\SubscriptionEditLayout;
use App\Orchid\Layouts\Promo\SubscriptionListLayout;
use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Promo\DTO\SubscriptionDto;
use App\Services\Promo\Enum\PromoSource;
use App\Services\Promo\SubscriptionService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class SubscriptionScreen extends Screen
{
    public string $name = 'Список email для рассылок';

    public function __construct(
        private readonly Request             $request,
        private readonly SubscriptionService $service,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => SubscriptionFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [ModalToggle::make('Добавить')
            ->class('mr-btn-success')
            ->icon('plus')
            ->modal('subscription_modal')
            ->modalTitle('Подписка')
            ->method('saveSubscription'),
        ];
    }

    public function layout(): iterable
    {
        return [
            SubscriptionFilter::displayFilterCard($this->request),
            SubscriptionListLayout::class,
            Layout::modal('subscription_modal', SubscriptionEditLayout::class),
        ];
    }

    public function saveSubscription(Request $request): void
    {
        $input = Validator::make($request->all(), [
            'subscription.language' => 'required|int',
            'subscription.event'    => 'required|string',
            'subscription.email'    => 'required|regex:' . RegexHelper::EMAIL_REGEX,
        ])->validate()['subscription'];

        $this->service->createSubscriptionWithNotify(
            new SubscriptionDto(
                email: $input['email'],
                language: Language::from((int)$input['language']),
                event: PromoEvent::from($input['event']),
                source: PromoSource::Admin,
            )
        );
    }

    public function deleteSubscription(int $subscription_id): void
    {
        $subscription = $this->service->getSubscriptionById($subscription_id);
        $this->service->deleteSubscription($subscription->token);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (SubscriptionFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        return redirect()->route('promo.subscriptions.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('promo.subscriptions.list');
    }
    #endregion
}
