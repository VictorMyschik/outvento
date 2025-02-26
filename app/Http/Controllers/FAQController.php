<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class FAQController extends Controller
{
    public function faqPage(): Application|Factory|View
    {
        $out['list'] = Faq::where('active', true)->where('language', $this->getLanguage()->value)->get()->all();

        return View('faq_page')->with($out);
    }
}
