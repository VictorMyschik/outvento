<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\Orchid\Attachment;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
use App\Orchid\Fields\CKEditor;
use App\Orchid\Layouts\Travel\InviteListLayout;
use App\Orchid\Layouts\Travel\TravelImageUploadLayout;
use App\Orchid\Layouts\User\UserBaseScreen;
use App\Services\Travel\Enum\Activity;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Tabs;
use Orchid\Support\Facades\Layout;

class UserTravelDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?Travel $travel = null;

    public function name(): string
    {
        return $this->travel->title ?? '';
    }

    public function description(): string
    {
        return $this->user->name . ' | ' . View('admin.created_updated', ['value' => $this->travel])->toHtml();
    }

    public function query(User $user, ?Travel $travel = null): iterable
    {
        return [
            'user'   => $user,
            'travel' => $travel,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.travels', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        $out[] = Layout::columns([
            $this->getBaseLayout(),
            $this->getRightTab(),
        ]);

        $out[] = Layout::rows([
            CKEditor::make('travel.description')->value($this->travel->description)->title('Подробное описание')->rows(5)->maxlength(8000),
            ViewField::make('')->view('space'),
            Button::make('Сохранить изменения')
                ->class('mr-btn-success pull-right')
                ->method('saveTravelDescription'),
        ]);

        $out[] = Layout::rows($this->getActionBottomLinkLayout());

        $out[] = Layout::modal('upload_travel_photo', TravelImageUploadLayout::class)->size(Modal::SIZE_LG);

        return $out;
    }

    private function getBaseLayout(): Rows
    {
        $membersOptions = [];
        foreach ($this->travelService->getTravelUsers($this->travel) as $user) {
            $membersOptions[$user->id] = $user->name;
        }

        $out = Layout::rows([
            Group::make([
                Label::make('travel.id')->title('ID')->value($this->travel->id ?? 'N/A'),
                Input::make('travel.date_from')
                    ->title('Date from')
                    ->type('date'),

                Input::make('travel.date_to')
                    ->title('Date to')
                    ->type('date'),
                Select::make('travel.user_id')
                    ->title('Владелец')
                    ->required()
                    ->value($this->travel->user_id ?? null)
                    ->options($membersOptions),
            ]),
            ViewField::make('')->view('space'),
            Group::make([
                Select::make('travel.status')->title('Общий статус')->required()->options(TravelStatus::getSelectList()),
                Select::make('travel.visible')->title('Видимость')->required()->options(TravelVisible::getSelectList()),
                ViewField::make('')->view('admin.link')->value([
                    'href'   => $this->travelService->getPublicUrl($this->travel),
                    'target' => '_blank',
                    'text'   => 'id: ' . $this->travel->getPublicId(),
                    'title'  => 'Публичная ссылка',
                ]),
            ]),

            Group::make([
                Select::make('travel.activities')
                    ->title('Activities')
                    ->value($this->travel->getActivitiesForOrchid())
                    ->options(Activity::getSelectList())
                    ->empty('Select travel type')
                    ->multiple(),

                Relation::make('travel.countries')
                    ->fromModel(Country::class, 'name_ru', 'id')
                    ->title('Countries')
                    ->value($this->travel->getCountriesForOrchid())
                    ->empty('Select countries')
                    ->multiple()
            ]),
            Input::make('travel.title')->title('Заголовок')->required()->maxlength(255),
            TextArea::make('travel.preview')->title('Короткое описание')->rows(3)->maxlength(355),
            ViewField::make('')->view('space'),
            Group::make([
                Input::make('travel.members')->title('Макс. участников')->max(32767)->type('number'),
            ]),

            ViewField::make('')->view('hr'),

            Button::make('Сохранить изменения')
                ->class('mr-btn-success pull-right')
                ->method('saveTravel'),
        ]);

        return $out;
    }

    private function getRightTab(): Tabs
    {
        return Layout::tabs([
            'Фото'       => Layout::rows($this->getPhotoTab()),
            //'Активные'   => Layout::rows([$this->getUITActiveListLayout()]),
            //'Отказ'      => Layout::rows([$this->getUITNotActiveListLayout()]),
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
                    ->method('saveTravelPhoto'),

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

    public function saveTravelPhoto(Request $request): void
    {
        $imageAttachIds = $request->all()['travel']['images'] ?? [];

        foreach (Attachment::whereIn('id', $imageAttachIds)->orderBy('sort')->get()->all() as $attachment) {
            $path = Storage::path($attachment->getFullPath());

            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                throw new \Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }

            $uploadedFile = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);

            $this->travelService->saveTravelImage($this->travel, $uploadedFile, ImageType::PHOTO);

            $attachment->delete();
        }
    }


    public function saveTravelDescription(Request $request): void
    {
        $description = $request->input('travel.description');

        $this->travelService->updateTravel($this->travel->id, [
            'description' => $description,
        ]);
    }

    public function getActionBottomLinkLayout(): array
    {
        return [
            Group::make([
                Button::make('Clone')
                    ->class('mr-btn-success  pull-left')
                    ->confirm('Are you sure you want to clone this travel?')
                    ->method('cloneTravel'),
                Button::make('Delete')
                    ->class('mr-btn-danger pull-right')
                    ->confirm('Are you sure you want to delete this travel?')
                    ->method('deleteTravel'),
            ]),
        ];
    }

    public function cloneTravel(): RedirectResponse
    {
        $id = $this->travelService->cloneTravel($this->travel);

        return redirect()->route('profiles.travel.details', ['user' => $this->user->id, 'travel' => $id]);
    }

    public function saveTravel(Request $request): void
    {
        $input = $request->all()['travel'];

        $this->travelService->updateTravel($this->travel->id, [
            'date_from' => $input['date_from'],
            'date_to'   => $input['date_to'],
            'status'    => $input['status'],
            'visible'   => $input['visible'],
            'title'     => $input['title'],
            'preview'   => $input['preview'],
            'members'   => $input['members'] ?? 0,
        ]);

        $this->travelService->updateTravelCountries($this->travel->id, $input['countries'] ?? []);
        $this->travelService->updateTravelActivities($this->travel->id, $input['activities'] ?? []);

        $this->travelService->updateTravelOwner($this->travel->id, (int)$request->input('travel')['user_id']);
    }

    public function deleteTravel(): RedirectResponse
    {
        $this->travelService->deleteTravel($this->travel->id);

        return redirect()->route('profiles.travels', ['user' => $this->user->id]);
    }
}