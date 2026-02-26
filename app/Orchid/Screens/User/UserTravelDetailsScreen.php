<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\Orchid\Attachment;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Models\Travel\TravelPoint;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Orchid\Fields\CKEditor;
use App\Orchid\Layouts\Travel\DescriptionPointShowLayout;
use App\Orchid\Layouts\Travel\InviteByEmailEditLayout;
use App\Orchid\Layouts\Travel\InviteListLayout;
use App\Orchid\Layouts\Travel\TravelMediaUploadLayout;
use App\Orchid\Layouts\Travel\TravelPointLayout;
use App\Orchid\Layouts\User\UserBaseScreen;
use App\Services\System\Enum\Language;
use App\Services\Travel\DTO\TravelPointDto;
use App\Services\Travel\Enum\Activity;
use App\Services\Travel\Enum\MediaType;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use App\Services\Travel\Enum\UITStatus;
use App\Services\User\Google\DTO\CityLocationDto;
use DB;
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
use Orchid\Support\Facades\Toast;

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
        $this->setAvatar($travel->getAvatarExt());

        return [
            'user'           => $user,
            'travel'         => $travel,
            'travel_invites' => $this->inviteService->getListByTravel($travel->id()),
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

        $out[] = Layout::modal('upload_travel_photo', TravelMediaUploadLayout::class)->async('asyncTravelMedia')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('new_invite_email_modal', InviteByEmailEditLayout::class);
        $out[] = Layout::modal('point_edit_modal', TravelPointLayout::class)->size(Modal::SIZE_LG)->async('asyncTravelPoint');
        $out[] = Layout::modal('description_modal', DescriptionPointShowLayout::class)->size(Modal::SIZE_LG)->async('asyncTravelPointDescription')->withoutApplyButton();

        return $out;
    }

    public function asyncTravelPointDescription(int $pointId): array
    {
        return [
            'description' => TravelPoint::loadBy($pointId)->description,
        ];
    }

    public function asyncTravelPoint(int $pointId = 0): array
    {
        $point = TravelPoint::loadBy($pointId);
        if ($point) {
            $city = $point->getCity();

            $out = $point->toArray();
            $out['city_country_code'] = trim($point->getCity()->getCountry()->getCode());
            $out['city_name'] = $city->getName($this->user->getLanguage());
            $out['city_lat'] = $city->lat;
            $out['city_lng'] = $city->lng;
            $out['city_place_id'] = $city->place_id;

            $coords = DB::selectOne(
                'SELECT ST_Y(?) as lat, ST_X(?) as lng',
                [$point->point, $point->point]
            );

            $out['lat'] = $coords->lat;
            $out['lng'] = $coords->lng;
        } else {
            $point = $this->user->getUserLocation()?->getCity();
            $out['lat'] = $point?->lat;
            $out['lng'] = $point?->lng;
        }

        $out['languageCode'] = $this->user->getLanguage()->getCode();
        $out['languageLabel'] = $this->user->getLanguage()->getLabel();

        return $out;
    }

    public function asyncTravelMedia(int $mediaId = 0): array
    {
        return [
            'media' => TravelMedia::loadBy($mediaId),
        ];
    }

    private function getBaseLayout(): Rows
    {
        $membersOptions = [];
        foreach ($this->travelService->getTravelUsers($this->travel) as $user) {
            $membersOptions[$user->id] = $user->name;
        }

        return Layout::rows([
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
                Select::make('travel.language')
                    ->title('Language')
                    ->required()
                    ->value($this->travel->language ?? null)
                    ->options(Language::getSelectList()),
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
                    ->multiple(),
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
    }

    private function getRightTab(): Tabs
    {
        return Layout::tabs([
            'Photo'   => Layout::rows($this->getPhotoTab()),
            'Points'  => Layout::rows($this->getTravelPointsLayout()),
            'Users'   => Layout::rows($this->getUITActiveListLayout()),
            //'Rejected'      => Layout::rows([$this->getUITNotActiveListLayout()]),
            'Invites' => InviteListLayout::class,
        ]);
    }

    private function getTravelPointsLayout(): array
    {
        $list = $this->travelService->getTravelPoints($this->travel->id);

        $btns = [
            ModalToggle::make('Добавить точку')
                ->class('mr-btn-success')
                ->modal('point_edit_modal')
                ->modalTitle('Edit point')
                ->method('setPoint', ['pointId' => 0]),
            Button::make('Удалить все точки')
                ->class('mr-btn-danger')
                ->confirm('Delete all points?')
                ->method('deleteTravelPoint')
        ];

        /** @var TravelPoint $point */
        foreach ($list as $key => &$point) {
            $point->descriptionModal = $point->description ? ModalToggle::make('')
                ->icon('eye')
                ->modal('description_modal', ['pointId' => $point->id])
                ->modalTitle('Description for point: ' . $point->getCity()->getName($this->user->getLanguage())) : '';

            $point->btn = DropDown::make()->icon('options-vertical')->list([
                ModalToggle::make('Edit')
                    ->icon('pencil')
                    ->modal('point_edit_modal')
                    ->modalTitle('Edit point')
                    ->method('setPoint', ['pointId' => $point->id]),
                Button::make('Delete')
                    ->icon('bs.trash3')
                    ->confirm('Удалить точку?')
                    ->method('deleteTravelPoint', ['id' => $point->id])
            ])->render();
        }

        return [
            Group::make($btns)->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.travel.points')->value($list)
        ];
    }

    public function deleteTravelPoint(int $pointId = 0): void
    {
        if ($pointId) {
            $this->travelService->deleteTravelPoint($pointId);
        } else {
            $this->travelService->deleteTravelPoints($this->travel->id);
        }
    }

    private function getUITActiveListLayout(): array
    {
        $list = $this->travelService->getTravelUsers($this->travel);

        /** @var UIT $userInTravel */
        foreach ($list as $key => &$userInTravel) {
            if ($userInTravel->status !== UITStatus::Confirmed->value) {
                unset($list[$key]);
            }

            $userInTravel->btn = DropDown::make()->icon('options-vertical')->list([
                Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->confirm('Удалить участника из списка')
                    ->method('removeUIH', ['id' => $userInTravel->id])
            ])->render();
        }

        return [
            Group::make([
                ViewField::make('')->view('admin.h6')->value('<b>Пригласить участников: </b>'),
                Link::make('QR code')->class('mr-btn-success')->icon('qrcode')->target('_blank')
                    ->href('https://api.qrserver.com/v1/create-qr-code/?data=' . $this->travelService->getPublicUrl($this->travel) . '&amp;size=200x200'),

                ModalToggle::make('by Email')
                    ->icon('envelope')
                    ->class('mr-btn-success')
                    ->modal('new_invite_email_modal')
                    ->modalTitle('Create invite')
                    ->method('createInvite'),
            ])->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.travel.users')->value($list)
        ];
    }

    private function getPhotoTab(): array
    {
        $mediaList = $this->travelService->getTravelMediaList($this->travel->id());

        $photoTab = [
            Group::make([
                ModalToggle::make('Загрузить фото')
                    ->class('mr-btn-success')
                    ->modal('upload_travel_photo')
                    ->modalTitle('Загрузить фото')
                    ->method('saveTravelPhoto'),

                Button::make('Удалить все фото')
                    ->method('deleteAllTravelPhotos')
                    ->novalidate()
                    ->class('mr-btn-danger')
                    ->canSee(count($mediaList) > 0)
                    ->confirm('Вы уверены, что хотите удалить все фото?')
                    ->parameters(['travelId' => $this->travel->id()]),

                ViewField::make('')->view('admin.raw')->class('')->value('Full size: ' . $this->travelService->getFullTravelMediaSizeInMb($this->travel->id()) . ' Mb'),
            ])->autoWidth(),
        ];

        $rows = [];
        /** @var TravelMedia $item */
        foreach (array_chunk($mediaList, 2) as $chunkRow) {
            $block = [];

            foreach ($chunkRow as $key => $item) {
                $block[$key]['data'] = ViewField::make('')->view('admin.travel.photo_block')->value([
                    'image' => ViewField::make('#')->view('admin.travel.photo')
                        ->value([
                            'path'      => route('api.v1.travel.image', [
                                'travel' => $this->travel->id,
                                'media'  => $item->id
                            ]),
                            'is_avatar' => $item->is_avatar,
                        ]),
                    'table' => ViewField::make('table')->view('admin.travel.photo_data')->value(['photo' => TravelMedia::loadBy($item->id)]),
                ]);

                $block[$key]['actions'] = Group::make([
                    ModalToggle::make('изменить')
                        ->icon('pencil')
                        ->modal('upload_travel_photo')
                        ->modalTitle('Изменить описание')
                        ->method('saveTravelPhoto', ['mediaId' => $item->id]),

                    Button::make('Сделать главной')
                        ->icon('star')
                        ->method('setAsLogo')
                        ->confirm('Сделать главной?')
                        ->parameters(['imageId' => $item->id]),

                    Button::make('удалить')->icon('trash')
                        ->method('deleteGoodMedia')
                        ->novalidate()
                        ->confirm('Удалить?')
                        ->parameters(['imageId' => $item->id]),
                ])->autoWidth();
            }

            $rows[] = ViewField::make('')->view('admin.travel.photo_tab')->value($block);
        }

        return array_merge(
            $photoTab,
            [ViewField::make('')->view('hr')],
            $rows
        );
    }

    public function deleteTravelPhoto(int $travelId): void
    {
        $this->travelService->deleteTravelMedias($travelId);
    }

    public function setAsLogo(int $imageId): void
    {
        $this->travelService->setAsLogo($imageId);
    }

    public function deleteGoodMedia(int $imageId): void
    {
        $this->travelService->deleteImage($imageId);
    }

    public function saveTravelPhoto(Request $request, int $mediaId = 0): void
    {
        $imageAttachIds = $request->all()['travel']['images'] ?? [];

        if ($mediaId) {
            $data = [
                'sort'        => $request->input('media')['sort'] ?? null,
                'description' => $request->input('media')['description'] ?? null,
            ];

            if ($request->input('media')['is_avatar'] ?? false) {
                $this->setAsLogo($mediaId);
            } else {
                $data['is_avatar'] = false;
            }

            $this->travelService->updateTravelMedia($mediaId, $data);

            return;
        }


        try {
            foreach (Attachment::whereIn('id', $imageAttachIds)->orderBy('sort')->get()->all() as $attachment) {
                $path = Storage::path($attachment->getFullPath());

                if (!file_exists($path) || !is_file($path)) {
                    Attachment::where('hash', $attachment->getHash())->delete();
                    throw new \Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
                }

                $uploadedFile = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);

                $mediaType = match ($uploadedFile->getMimeType()) {
                    'image/jpeg', 'image/png', 'image/gif' => MediaType::Image,
                    'video/mp4', 'video/avi', 'video/mpeg' => MediaType::Video,
                    default => throw new \Exception('Unsupported file type: ' . $uploadedFile->getMimeType()),
                };

                $this->travelService->saveTravelMedia($mediaId, $this->travel, $uploadedFile, $mediaType);

                $attachment->delete();
            }
        } catch (\Exception $e) {
            Attachment::whereIn('id', $imageAttachIds)->delete();
            Toast::error($e->getMessage());
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

    public function deleteAllTravelPhotos(): void
    {
        $this->travelService->deleteTravelMedias($this->travel->id);
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
            'language'  => $input['language'],
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

    public function createInvite(): void
    {
        $email = request()->validate([
            'email' => 'required|email|max:255'
        ])['email'];

        $this->inviteService->invite($this->travel, $email);

        Toast::info('Приглашение отправлено')->delay(1000);;
    }

    public function removeTravelInvite(int $inviteId): void
    {
        $this->inviteService->removeTravelInvite($inviteId);
    }

    public function resendTravelInvite(int $inviteId): void
    {
        $this->inviteService->reSendTravelInvite($inviteId);

        Toast::info('Приглашение повторно отправлено')->delay(1000);;
    }

    public function setPoint(Request $request, int $pointId = 0): void
    {
        $this->travelService->savePoint(
            pointId: $pointId,
            type: TravelPointType::from((int)$request->input('type')),
            dto: new CityLocationDto(
                placeId: $request->input('city_place_id'),
                lat: (float)$request->input('city_lat'),
                lng: (float)$request->input('city_lng'),
                countryCode: $request->input('city_country_code'),
                cityName: $request->input('city_name'),
                language: Language::fromCode(app()->getLocale()),
            ),
            data: new TravelPointDto(
                travelId: $this->travel->id,
                address: $request->input('address'),
                position: (int)$request->input('position'),
                rating: (int)$request->input('rating'),
                lat: (float)$request->input('lat'),
                lng: (float)$request->input('lng'),
                description: $request->input('description'),
            )
        );
    }
}