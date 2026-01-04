<?php

namespace App\Orchid\Screens\Subscription;

use App\Helpers\RegexHelper;
use App\Orchid\Filters\EmailSubscriptionFilter;
use App\Orchid\Layouts\Subscription\SubscriptionEditLayout;
use App\Orchid\Layouts\Subscription\SubscriptionListLayout;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Subscription\SubscriptionService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class SubscriptionScreen extends Screen
{
    public function __construct(private readonly Request $request, private readonly SubscriptionService $service) {}

    public function name(): ?string
    {
        return 'Список email для рассылок';
    }

    public function query(): iterable
    {
        return [
            'list' => EmailSubscriptionFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [ModalToggle::make('Добавить')
            ->class('mr-btn-success')
            ->icon('plus')
            ->modal('subscription_modal')
            ->modalTitle('Подписка')
            ->method('saveSubscription')
            ->asyncParameters(['subscription_id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            EmailSubscriptionFilter::displayFilterCard($this->request),
            SubscriptionListLayout::class,
            Layout::modal('subscription_modal', SubscriptionEditLayout::class)->async('asyncGetSubscription'),
        ];
    }

    public function asyncGetSubscription(int $subscription_id = 0): array
    {
        $optionExists = [];
        if ($subscription_id !== 0) {
            $current = $this->service->getSubscriptionById($subscription_id);
            foreach ($this->service->getSubscriptionByEmail($current->getEmail()) as $subscription) {
                $optionExists[$subscription->getType()->value] = $subscription->getType()->getLabel();
            }
        }
        $this->service->getSubscriptionById($subscription_id);

        return [
            'type_options'        => [EmailTypeEnum::News->value => EmailTypeEnum::News->getLabel()],
            'type_options_exists' => $optionExists,
            'subscription'        => $this->service->getSubscriptionById($subscription_id),
        ];
    }

    public function saveSubscription(Request $request, int $subscription_id): void
    {
        $input = Validator::make($request->all(), [
            'subscription.language' => 'required|int',
            'subscription.type'     => 'required|array',
            'subscription.email'    => 'required|regex:' . RegexHelper::EMAIL_REGEX,
        ])->validate()['subscription'];

        $types = $input['type'];
        $email = $input['email'];

        if ($subscription_id !== 0) {
            $old = $this->service->getSubscriptionById($subscription_id);
            $this->service->deleteSubscriptionByEmail($old->getEmail());
        }

        if ($list = $this->service->getSubscriptionByEmail($email)) {
            foreach ($list as $subscription) {
                $key = array_search($subscription->getType()->value, $types);
                if ($key !== false) {
                    unset($types[$key]);
                }
            }
        }

        foreach ($types as $type) {
            $this->service->createSubscription(EmailTypeEnum::from($type), ['email' => $email, 'language' => $input['language']]);
        }
    }

    public function deleteSubscription(int $subscription_id): void
    {
        $subscription = $this->service->getSubscriptionById($subscription_id);
        $this->service->deleteSubscription($subscription->getToken());
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (EmailSubscriptionFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('subscriptions.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('subscriptions.list');
    }
    #endregion
}
