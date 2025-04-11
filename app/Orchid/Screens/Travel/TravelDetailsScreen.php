<?php

namespace App\Orchid\Screens\Travel;

use App\Helpers\TouchUserUpdateEvent;
use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGoodDetail;
use App\Models\Catalog\CatalogImage;
use App\Models\EmailInvite;
use App\Models\Orchid\Attachment;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\Travel\TravelType;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Orchid\Layouts\Catalog\GoodUploadEditLayout;
use App\Orchid\Layouts\Travel\InviteByEmailEditLayout;
use App\Orchid\Layouts\Travel\InviteListLayout;
use App\Orchid\Layouts\Travel\TravelEditLayout;
use App\Orchid\Layouts\Travel\TravelImageUploadLayout;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use App\Services\Email\EmailService;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use App\Services\Travel\Enum\UITStatus;
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
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
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
        return '';
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
            Group::make([
                ViewField::make('')->view('admin.created_updated')->value($this->travel),
                Button::make('Clear')->confirm('Удалить?')->class('mr-btn-danger pull-right')->name('Delete')->method('remove')->novalidate(),
            ])->fullWidth()
        ]);

        $out[] = Layout::modal('upload_travel_photo', TravelImageUploadLayout::class)->size(Modal::SIZE_LG);

        return $out;
    }

    private function getBaseLayout(): Rows
    {
        return Layout::rows([
            Group::make([
                Select::make('travel.status')->title('Общий статус')->required()->options(TravelStatus::getSelectList()),
                Select::make('travel.visible_type')->title('Видимость')->required()->empty('Select travel public type')->options(TravelVisibleType::getSelectList()),
                Select::make('travel.travel_type_id')->title('Тип')->required()->empty('Select travel type')->fromModel(TravelType::class, 'name_ru'),
                Select::make('travel.country_id')->title('Страна')->required()->empty('Select country')->options(Country::all()->pluck('name_ru', 'id')->toArray()),
            ]),
            ViewField::make('')->view('space'),
            Input::make('travel.title')->title('Заголовок')->required()->maxlength(255),
            TextArea::make('travel.preview')->title('Короткое описание')->rows(5)->maxlength(500),
            ViewField::make('')->view('space'),
            Group::make([
                Input::make('travel.date_from')
                    ->title('Date from')
                    ->required()
                    ->type('date'),

                Input::make('travel.date_to')
                    ->title('Date to')
                    ->required()
                    ->type('date'),
                Input::make('travel.members')->title('Макс. участников')->type('number'),
            ]),
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
            'Фото'       => Layout::rows($this->getPhotoTab()),
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

    private function getPhotoTab(): array
    {
        $logo = $this->travelService->getTravelLogo($this->travel->id());
        $photoList = $this->travelService->getTravelPhotoList($this->travel->id());

        if ($logo) {
            $photoList = array_merge([$logo], $photoList);
        }

        $photoTab = [
            Group::make([
                ModalToggle::make('Загрузить фото')
                    ->class('mr-btn-success')
                    ->modal('upload_travel_photo')
                    ->modalTitle('Загрузить фото')
                    ->method('saveTravelPhoto', ['travelId' => $this->travel->id()]),

                Button::make('Удалить все фото')
                    ->method('deleteTravelPhoto')
                    ->novalidate()
                    ->class('mr-btn-danger')
                    ->canSee(count($photoList) > 0)
                    ->confirm('Вы уверены, что хотите удалить все фото?')
                    ->parameters(['travelId' => $this->travel->id()]),
            ])->autoWidth(),
        ];

        $group = [];

        /** @var TravelImage $img */
        foreach ($photoList as $img) {
            $group[] = Group::make([
                ViewField::make('#')->view('admin.travel.photo')->value(['path' => $img->getUrl(), 'is_logo' => $img->getType() === ImageType::LOGO]),
                ViewField::make('table')->view('admin.travel.photo_data')->value(['photo' => $img]),

                DropDown::make()->icon('options-vertical')->list([
                    ModalToggle::make('изменить')->icon('pencil')->modal('upload_travel_photo')
                        ->modalTitle('Изменить описание')
                        ->method('saveGoodPhoto', ['catalog_image_id' => $img->id()]),

                    Button::make('Сделать главной')->icon('star')
                        ->method('setAsLogo')
                        ->confirm('Сделать главной?')
                        ->parameters(['travelId' => $this->travel->id(), 'imageId' => $img->id()]),

                    Button::make('удалить')->icon('trash')->method('deleteGoodPhoto')->novalidate()
                        ->confirm('Удалить фото?')
                        ->parameters(['travelId' => $this->travel->id(), 'imageId' => $img->id()]),
                ]),
            ])->autoWidth()->alignStart();
        }

        return array_merge($photoTab, [ViewField::make('')->view('space')], $group);
    }

    public function deleteTravelPhoto(int $travelId): void
    {
        $this->travelService->deleteTravelImages($travelId);
    }

    public function setAsLogo(int $travelId, int $imageId): void
    {
        $this->travelService->setAsLogo($travelId, $imageId);
    }

    public function deleteGoodPhoto(int $travelId, int $imageId): void
    {
        $this->travelService->deleteImage($imageId);
    }

    public function saveTravelPhoto(Request $request, int $travelId): void
    {
        $imageAttachIds = $request->all()['travel']['image'] ?? [];

        $travel = Travel::loadByOrDie($travelId);

        foreach (Attachment::whereIn('id', $imageAttachIds)->orderBy('sort')->get()->all() as $attachment) {
            $this->travelService->saveTravelImage($travel, $attachment, ImageType::PHOTO);
        }
    }

    private function getUITActiveListLayout(): ViewField
    {
        $list = $this->travelService->getTravelUsers($this->travel);

        /** @var UIT $userInTravel */
        foreach ($list as $key => &$userInTravel) {
            if ($userInTravel->getStatus() !== UITStatus::CONFIRMED) {
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
            'travel.preview'        => 'nullable|string|max:500',
            'travel.members'        => 'nullable|integer',
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
