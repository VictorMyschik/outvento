<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\System\MrMessageHelper;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function subscribe(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->only(['email']), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            abort(400, $validator->errors()->first());
        }
        $data = $validator->validated();

        $this->subscriptionService->createNewsSubscription($data['email'], $this->getLanguage());

        MrMessageHelper::setMessage(MrMessageHelper::KIND_SUCCESS, 'Subscription successful!');
        return back();
    }
}
