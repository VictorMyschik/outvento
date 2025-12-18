<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Wishlist;

use App\Helpers\RegexHelper;
use App\Models\Wishlist\Wishlist;
use App\Orchid\Filters\EmailWishlistFilter;
use App\Orchid\Filters\WishlistFilter;
use App\Orchid\Layouts\Wishlist\WishlistEditLayout;
use App\Orchid\Layouts\Wishlist\WishlistListLayout;
use App\Services\Email\Enum\EmailTypeEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;

class WishlistScreen extends Screen
{
    public function __construct(private readonly Request $request) {}

    public function name(): ?string
    {
        return 'Список желаний';
    }

    public function query(): iterable
    {
        return [
            'list' => WishlistFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [ModalToggle::make('Добавить')
            ->class('mr-btn-success')
            ->icon('plus')
            ->modal('subscription_modal')
            ->modalTitle('Добавить желание')
            ->method('saveWishlist')
            ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            WishlistFilter::displayFilterCard($this->request),
            WishlistListLayout::class,
            //  Layout::modal('wishlist_modal', WishlistEditLayout::class)->async('asyncGetWishlist'),
        ];
    }

    public function asyncGetWishlist(int $id = 0): array
    {
        return [
            'wishlist' => Wishlist::loadBy($id),
        ];
    }

    public function saveWishlist(Request $request, int $subscription_id): void
    {
        $input = Validator::make($request->all(), [
            'subscription.language' => 'required|int',
            'subscription.type'     => 'required|array',
            'subscription.email'    => 'required|regex:' . RegexHelper::EMAIL_REGEX,
        ])->validate()['subscription'];

        $types = $input['type'];
        $email = $input['email'];

        if ($subscription_id !== 0) {
            $old = $this->service->getWishlistById($subscription_id);
            $this->service->deleteWishlistByEmail($old->getEmail());
        }

        if ($list = $this->service->getWishlistByEmail($email)) {
            foreach ($list as $subscription) {
                $key = array_search($subscription->getType()->value, $types);
                if ($key !== false) {
                    unset($types[$key]);
                }
            }
        }

        foreach ($types as $type) {
            $this->service->createWishlist(EmailTypeEnum::from($type), ['email' => $email, 'language' => $input['language']]);
        }
    }

    public function deleteWishlist(int $subscription_id): void
    {
        $subscription = $this->service->getWishlistById($subscription_id);
        $this->service->deleteWishlist($subscription->getToken());
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (EmailWishlistFilter::FIELDS as $item) {
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
