<?php

namespace App\Orchid\Screens\Travel;

use App\Classes\Email\EmailService;
use App\Models\EmailInvite;
use App\Models\Travel;
use App\Models\UIH;
use App\Orchid\Layouts\Travel\InviteByEmailEditLayout;
use App\Orchid\Layouts\Travel\InviteListLayout;
use App\Orchid\Layouts\Travel\TravelEditLayout;
use App\Orchid\Layouts\Travel\UIHActiveListLayout;
use App\Orchid\Layouts\Travel\UIHNotActiveListLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelDetailsScreen extends Screen
{
    public ?Travel $travel = null;

    public function query(Travel $travel): array
    {
        return [
            'travel'         => $travel,
            'active-uih'     => UIH::filters([])->where('travel_id', $travel->id())->where('status', UIH::STATUS_APPROVED)->paginate(20),
            'not-active-uih' => UIH::filters([])->where('travel_id', $travel->id())->where('status', UIH::STATUS_REJECTED)->paginate(20),
            'invite-uih'     => EmailInvite::filters([])->where('travel_id', $travel->id())->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return $this->travel?->getName();
    }

    public function description(): ?string
    {
        return $this->travel?->getDescription();
    }

    public function commandBar(): iterable
    {
        $id = (int)$this->travel?->id();

        return [
            ModalToggle::make('Edit')
                ->type(Color::BASIC())
                ->icon('pencil')
                ->modal('travel_modal')
                ->modalTitle('Edit travel id')
                ->method('saveTravel')
                ->asyncParameters(['id' => $id]),

            Button::make(__('Delete'))
                ->icon('bs.trash3')
                ->confirm(__('Are you sure you want to delete the travel?'))
                ->method('remove', ['id' => $id]),
        ];
    }

    public function layout(): iterable
    {
        $out = [
            Layout::modal('travel_modal', TravelEditLayout::class)->async('asyncGetTravel'),
        ];

        if ($travel = Travel::loadBy((int)$this->travel?->id())) {
            $publicLink = route('travel.public.link', ['token' => $travel->getPublicId()]);

            $out[] = Layout::modal('new_invite_email_modal', InviteByEmailEditLayout::class);

            $rows = [
                'Статус'             => $travel->getStatusName(),
                'Страна'             => $travel->getCountry()->getName(),
                'Тип похода'         => $travel->getTravelType()->getName(),
                'Тип публичности'    => $travel->getVisibleKindName(),
                'Публичная страница' => Travel::VISIBLE_KIND_FOR_ME !== $travel->getVisibleKind() ? "<a target='_blank' href='" . $publicLink . "'>$publicLink</a>" : 'Публичная страница не доступна',
                //'Снаряжение'         => '<a href="'.route().'">Снаряжение</a>',
            ];
            $columns[] = Layout::view('table_travel_details', ['rows' => $rows]);

            if (Travel::VISIBLE_KIND_FOR_ME !== $travel->getVisibleKind()) {
                $columns[] = Layout::tabs([
                    'Активные'   => UIHActiveListLayout::class,
                    'Отказ'      => UIHNotActiveListLayout::class,
                    'В ожидании' => InviteListLayout::class,
                    'Пригласить' => Layout::rows([
                        Group::make([
                            Link::make('QR code')->icon('qrcode')->target('_blank')->type(Color::INFO())
                                ->href('https://api.qrserver.com/v1/create-qr-code/?data=' . $publicLink . '&amp;size=200x200'),

                            ModalToggle::make('Email')
                                ->type(Color::INFO())
                                ->modal('new_invite_email_modal')
                                ->modalTitle('Create new invite by email')
                                ->method('createNewInvite')
                                ->asyncParameters(['id' => $travel->id()]),
                        ])->autoWidth()
                    ])
                ])->activeTab('Активные');
            }

            $out[] = Layout::columns($columns);

            // Images
            $images = $this->travel->getImagesList();

            $out[] = Layout::view('travel_images', ['images' => $images]);
        }

        return $out;
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
            'uih' => UIH::loadBy($id) ?: new UIH()
        ];
    }

    public function saveTravel(Request $request): void
    {
        $data = $request->validate([
            'travel.name'           => 'required|string|max:255',
            'travel.description'    => 'nullable|string|max:8000',
            'travel.status'         => 'required|integer',
            'travel.user_id'        => 'required|integer',
            'travel.country_id'     => 'required|integer',
            'travel.travel_type_id' => 'required|integer',
            'travel.visible_kind'   => 'required|integer',
        ])['travel'];

        $travel = Travel::loadBy($request->get('id')) ?: new Travel();
        $travel->fill($data);
        $travel->save_mr();

        Toast::info('Travel was saved');
    }

    public function saveUIH(Request $request): void
    {
        $data = $request->validate([
            'uih.status' => 'required|integer',
        ])['uih'];

        UIH::updateOrCreate(
            ['id' => (int)$request->get('id')],
            $data
        );

        Toast::info('UIH was saved');
    }

    public function remove(int $id): RedirectResponse
    {
        Travel::loadBy($id)?->delete();

        return redirect()->route('travel.list');
    }

    public function removeUIH(int $id): void
    {
        UIH::loadBy($id)?->delete();
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
        $invite->save_mr();

        EmailService::sendTravelInvite($invite);

        Toast::info('Приглашение отправлено');
    }

    public function resendEmailInvite(int $id): void
    {
        $invite = EmailInvite::loadByOrDie($id);
        EmailService::sendTravelInvite($invite);
        Toast::info('Приглашение отправлено');
    }

    public function declineUIH(int $id): void
    {
        $invite = UIH::loadByOrDie($id);
        $invite->setStatus(UIH::STATUS_REJECTED);
        $invite->save_mr();

        Toast::info('Приглашение отклонено');
    }
}
