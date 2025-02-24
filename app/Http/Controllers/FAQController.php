<?php

namespace App\Http\Controllers;

use App\Helpers\System\MrMessageHelper;
use App\Mail\Feedback;
use App\Models\Faq;
use App\Models\System\Language;
use App\Models\System\Settings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FAQController extends Controller
{
    public function faqPage(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $language = Language::where('code', strtoupper(app()->getLocale()))->first();
        $out['list'] = Faq::where('language_id', $language->id())->get()->all();

        return View('faq_page')->with($out);
    }

    public function sendQuestion(Request $request): RedirectResponse
    {
        $input = $request->all();

        $data = [
            'name'  => $input['name'],
            'email' => $input['email'],
            'text'  => $input['text'],
        ];

        Mail::to(Settings::loadAdminEmailToNotify())->queue(new Feedback($data));

        MrMessageHelper::SetMessage(MrMessageHelper::KIND_SUCCESS, __('mr-t.feedback_send'));
        return back();
    }
}
