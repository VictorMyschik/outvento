<?php

namespace App\Http\Controllers\Travel\Travel;

use App\Http\Controllers\Controller;
use App\Models\EmailInvite;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Services\Travel\Enum\UITStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TravelInviteController extends Controller
{
    public function index(string $token, string $status): RedirectResponse
    {
        $token = substr($token, 0, 32);
        abort_if(!preg_match('/[a-z0-9]{32}/', $token), 404);

        /** @var EmailInvite $emailInvite */
        $emailInvite = EmailInvite::where('token', $token)->first();

        if (!$emailInvite) {
            return redirect('/');
        }

        $user = User::where('email', $emailInvite->getEmail())->first();

        if ($user) {
            Auth::login($user);

            $status = $status === 'true' ? UITStatus::CONFIRMED : UITStatus::REJECTED;

            UIT::where('travel_id', $emailInvite->getTravel()->id())->where('user_id', $user->id)->updateOrCreate([
                'travel_id' => $emailInvite->getTravel()->id(),
                'user_id'   => $user->id,
            ], [
                'status' => $status,
            ]);

            $publicId = $emailInvite->getTravel()->getPublicId();

            $emailInvite->delete();

            return redirect()->route('travel.public.link', ['token' => $publicId]);
        }

        if ($status === 'false') {
            return redirect('/');
        }

        Auth::logout();
        return redirect()->route('register')->withInput(['email' => $emailInvite->getEmail(), 'token' => $token]);
    }
}
