<?php

namespace App\Orchid\Screens\Travel;

use App\Models\EmailInvite;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelType;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionDeleteModelLayout;
use App\Orchid\Layouts\Travel\InviteByEmailEditLayout;
use App\Orchid\Layouts\Travel\InviteListLayout;
use App\Orchid\Layouts\Travel\TravelEditLayout;
use App\Services\Email\EmailService;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\UITStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use App\Services\Travel\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Tabs;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelDetailsScreen extends Screen
{
    public ?Travel $travel = null;

    public function __construct(private readonly TravelService $travelService) {}

    public function query(Travel $travel): array
    {
        return [
            'travel'     => $travel,
            'invite-uih' => EmailInvite::filters([])->where('travel_id', $travel->id())->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return $this->travel?->getTitle();
    }

    public function description(): ?string
    {
        return View('admin.created_updated', ['model' => $this->travel])->toHtml();
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')->icon('check')->method('saveTravel')->class('mr-btn-success'),
            Link::make('Назад')->icon('arrow-left')->route('travel.list'),
        ];
    }

    public function layout(): iterable
    {
        $out[] = Layout::columns([
            $this->getBaseLayout(),
            $this->getRightTab(),
        ]);

        $out[] = Layout::rows([
            Quill::make('travel.description')->title('Подробное описание')->rows(5)->maxlength(8000),
        ]);

        $out[] = Layout::modal('travel_modal', TravelEditLayout::class)->async('asyncGetTravel');
        $out[] = Layout::modal('new_invite_email_modal', InviteByEmailEditLayout::class);

        $out[] = Layout::rows([
            ActionDeleteModelLayout::getActionButtons('удалить'),
        ]);

        return $out;
    }

    private function getBaseLayout(): Rows
    {
        return Layout::rows([
            Group::make([
                Select::make('travel.status')->title('Общий статус')->required()->options(TravelStatus::getSelectList()),
                Select::make('travel.visible_type')->title('Видимость')->required()->empty('Select travel public type')->options(TravelVisibleType::getSelectList()),
                Select::make('travel.travel_type_id')->title('Тип')->required()->empty('Select travel type')->options(TravelType::all()->pluck('name', 'id')->toArray()),
                Select::make('travel.country_id')->title('Страна')->required()->empty('Select country')->options(Country::all()->pluck('name', 'id')->toArray()),
            ]),
            ViewField::make('')->view('space'),
            Input::make('travel.title')->title('Заголовок')->required()->maxlength(255),
            ViewField::make('')->view('space'),
            Group::make([
                Select::make('travel.user_id')->title('Владелец')->required()->options(User::all()->pluck('name', 'id')->toArray()),
                Label::make('travel.public_id')->title('Публичный ID')->value($this->travel->getPublicId()),
                Link::make('link')->title('Ссылка на страницу')->href($this->travelService->getPublicUrl($this->travel))->target('_blank'),
            ]),
        ]);
    }

    private function getRightTab(): Tabs
    {
        return Layout::tabs([
            'Активные'   => Layout::rows([$this->getUITActiveListLayout()]),
            'Отказ'      => Layout::rows([$this->getUITNotActiveListLayout()]),
            'В ожидании' => InviteListLayout::class,
            'Пригласить' => Layout::rows([
                Group::make([
                    Link::make('QR code')->class('mr-btn-success')->icon('qrcode')->target('_blank')
                        ->href('https://api.qrserver.com/v1/create-qr-code/?data=' . $this->travelService->getPublicUrl($this->travel) . '&amp;size=200x200'),

                    ModalToggle::make('Email')
                        ->class('mr-btn-success')
                        ->modal('new_invite_email_modal')
                        ->modalTitle('Create new invite by email')
                        ->method('createNewInvite')
                        ->asyncParameters(['id' => $this->travel->id()]),
                ])->autoWidth()
            ])
        ]);
    }

    private function getUITActiveListLayout(): ViewField
    {
        $list = $this->travelService->getTravelUsers($this->travel);

        /** @var UIT $userInTravel */
        foreach ($list as $key => &$userInTravel) {
            if ($userInTravel->getStatus() !== UITStatus::APPROVED) {
                unset($list[$key]);
            }

            $userInTravel->btn = DropDown::make()->icon('options-vertical')->list([
                Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->confirm('Удалить участника из списка')
                    ->method('removeUIH', ['id' => $userInTravel->id()])
            ])->render();
        }

        return ViewField::make('')->view('admin.travel.users')->value($list);
    }

    private function getUITNotActiveListLayout(): ViewField
    {
        $list = $this->travelService->getTravelUsers($this->travel);

        foreach ($list as $key => &$userInTravel) {
            if ($userInTravel->getStatus() == UITStatus::REJECTED) {
                $userInTravel->btn = DropDown::make()->icon('options-vertical')->list([
                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm('Удалить участника из списка')
                        ->method('removeUIH', ['id' => $userInTravel->id()])
                ])->render();
                continue;
            }

            unset($list[$key]);
        }

        return ViewField::make('')->view('admin.travel.users')->value($list);
    }

    public function asyncGetTravel(int $id = 0): array
    {
        return [
            'travel' => Travel::loadBy($id) ?: new Travel()
        ];
    }

    public function asyncGetUIH(int $id = 0): array
    {
        return [
            'uih' => UIT::loadBy($id) ?: new UIT()
        ];
    }

    public function saveTravel(Request $request): void
    {
        $data = $request->validate([
            'travel.title'          => 'required|string|max:255',
            'travel.description'    => 'nullable|string|max:8000',
            'travel.status'         => 'required|integer',
            'travel.user_id'        => 'required|integer',
            'travel.country_id'     => 'required|integer',
            'travel.travel_type_id' => 'required|integer',
            'travel.visible_type'   => 'required|integer',
        ])['travel'];

        $this->travelService->updateTravel($this->travel->id(), $data);

        Toast::info('Travel was saved')->delay(1000);;
    }

    public function saveUIH(Request $request): void
    {
        $data = $request->validate([
            'uih.status' => 'required|integer',
        ])['uih'];

        UIT::updateOrCreate(
            ['id' => (int)$request->get('id')],
            $data
        );

        Toast::info('UIH was saved')->delay(1000);;
    }

    public function remove(int $id): RedirectResponse
    {
        Travel::loadBy($id)?->delete();

        return redirect()->route('travel.list');
    }

    public function removeUIH(int $id): void
    {
        (UIT::loadByOrDie($id))->delete();
    }

    public function createNewInvite(int $id): void
    {
        $email = request()->validate([
            'email' => 'required|email|max:255'
        ])['email'];

        $exists = DB::table(EmailInvite::getTableName())->where('email', $email)->where('travel_id', $id)->exists();
        if ($exists) {
            Toast::error('Приглашение уже отправлено');
            return;
        }


        $travel = Travel::loadByOrDie($id);

        $invite = new EmailInvite();
        $invite->setEmail($email);
        $invite->setTravelID($travel->id());
        $invite->setStatus(EmailInvite::STATUS_NEW);
        $invite->setToken($invite->generateToken());
        $invite->setUserID($travel->getUser()->id);
        $invite->save();

        EmailService::sendTravelInvite($invite);

        Toast::info('Приглашение отправлено')->delay(1000);;
    }

    public function resendEmailInvite(int $id): void
    {
        $invite = EmailInvite::loadByOrDie($id);
        EmailService::sendTravelInvite($invite);
        Toast::info('Приглашение отправлено')->delay(1000);;
    }

    public function declineUIH(int $id): void
    {
        $invite = UIT::loadByOrDie($id);
        $invite->setStatus(UITStatus::REJECTED);
        $invite->save();

        Toast::info('Приглашение отклонено')->delay(1000);;
    }
}
