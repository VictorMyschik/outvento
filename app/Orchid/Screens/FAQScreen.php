<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Faq;
use App\Orchid\Layouts\FAQ\FAQEditLayout;
use App\Orchid\Layouts\FAQ\FAQListLayout;
use App\Services\Other\Faq\FaqService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FAQScreen extends Screen
{
    public string $name = 'FAQ';

    public string $description = 'Часто задаваемые вопросы';

    public function __construct(private FaqService $service) {}

    public function query(): iterable
    {
        return [
            'list' => Faq::paginate(10)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('faq_modal')
                ->modalTitle('Create New FAQ')
                ->method('saveFAQ')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            FaqListLayout::class,
            Layout::modal('faq_modal', FAQEditLayout::class)->async('asyncGetFAQ')->size(Modal::SIZE_LG),
        ];
    }

    public function asyncGetFAQ(int $id = 0): array
    {
        return [
            'faq' => Faq::loadBy($id) ?: new Faq()
        ];
    }

    public function saveFAQ(Request $request, int $id): void
    {
        $data = $request->validate([
            'faq.active'   => 'required|boolean',
            'faq.language' => 'required|integer',
            'faq.title'    => 'required|string',
            'faq.text'     => 'required|string',
        ])['faq'];

        $this->service->saveFaq($id, $data);

        Toast::info('FAQ was saved')->delay(1500);
    }

    public function remove(int $id): void
    {
        Faq::loadBy($id)?->delete();
    }
}
