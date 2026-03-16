<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Orchid\Attachment;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelComment;
use App\Models\Travel\TravelMedia;
use App\Models\Travel\TravelPoint;
use App\Models\Travel\TravelResource;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Orchid\Fields\CKEditor;
use App\Orchid\Layouts\Travel\DescriptionPointShowLayout;
use App\Orchid\Layouts\Travel\InviteByEmailEditLayout;
use App\Orchid\Layouts\Travel\TravelCommentEditLayout;
use App\Orchid\Layouts\Travel\TravelMediaUploadLayout;
use App\Orchid\Layouts\Travel\TravelPointLayout;
use App\Orchid\Layouts\Travel\TravelResourceFileEditLayout;
use App\Orchid\Layouts\Travel\TravelResourceLinkEditLayout;
use App\Services\System\Enum\Language;
use App\Services\Travel\DTO\TravelPointDto;
use App\Services\Travel\Enum\Activity;
use App\Services\Travel\Enum\MediaType;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelResourceType;
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
use Orchid\Screen\Fields\DateTimer;
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
        $link = "<a href='" . route('profiles.details', ['user' => $this->user->id]) . "'>" . $this->user->name . "</a>";
        return $link . ' | ' . View('admin.created_updated', ['value' => $this->travel])->toHtml();
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

        $out[] = Layout::accordion([
            'Full description' => Layout::rows([
                CKEditor::make('travel.description')->value($this->travel->description)->rows(5)->maxlength(8000),
                ViewField::make('')->view('space'),
                Button::make('save')
                    ->class('mr-btn-success pull-right')
                    ->method('saveTravelDescription'),
            ]),
        ]);

        $out[] = Layout::accordion($this->getTravelCommentsLayout());
        $out[] = Layout::rows($this->getActionBottomLinkLayout());

        $out[] = Layout::modal('upload_travel_photo', TravelMediaUploadLayout::class)->async('asyncTravelMedia')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('new_invite_email_modal', InviteByEmailEditLayout::class);
        $out[] = Layout::modal('point_edit_modal', TravelPointLayout::class)->size(Modal::SIZE_LG)->async('asyncTravelPoint');
        $out[] = Layout::modal('description_modal', DescriptionPointShowLayout::class)->size(Modal::SIZE_LG)->async('asyncTravelPointDescription')->withoutApplyButton();
        $out[] = Layout::modal('travel_resource_link', TravelResourceLinkEditLayout::class)->async('asyncTravelResource');
        $out[] = Layout::modal('travel_resource_file', TravelResourceFileEditLayout::class)->async('asyncTravelResource');
        $out[] = Layout::modal('edit_comment_modal', TravelCommentEditLayout::class)->async('asyncGetTravelComment');

        return $out;
    }

    private function getTravelCommentsLayout(): array
    {
        $list = $this->travelCommentService->getTravelCommentsTree($this->travel->id);

        /** @var TravelComment $value */
        foreach ($list as $key => $comment) {
            $rows[] = [
                Layout::rows([
                    ViewField::make('')->view('admin.travel.comment')->value($comment),
                ]),
            ];
        }

        $rows[] = Layout::rows([
            CKEditor::make('content')->title('New Comment')->maxlength(8000),
            ViewField::make('')->view('space'),
            Group::make([
                Button::make('add')->class('mr-btn-success pull-left')->method('saveTravelComment'),
                Button::make('purge')
                    ->confirm('Are you sure? Will delete all comments for this travel!')
                    ->class('mr-btn-danger pull-right')
                    ->method('clearTravelComment'),
            ])
        ]);

        return [
            'Comments (' . count($list) . ')' => $rows
        ];
    }

    public function clearTravelComment(): void
    {
        $this->travelCommentService->purgeTravelComments($this->travel->id);
    }

    public function asyncGetTravelComment(int $commentId): array
    {
        return [
            'comment' => TravelComment::loadBy($commentId),
        ];
    }

    public function deleteTravelComment(int $commentId): void
    {
        $this->travelCommentService->deleteComment($commentId);
    }

    public function saveTravelComment(Request $request, int $commentId = 0, ?int $parentId = null): void
    {
        if ($commentId) {
            $this->travelCommentService->updateCommentContent(
                commentId: $commentId,
                content: $request->input('comment.content'),
            );

            return;
        }

        $this->travelCommentService->addComment(
            travelId: $this->travel->id,
            userId: $this->user->id,
            parentId: $parentId,
            comment: trim((string)($request->input('content') ?: $request->input('comment.content'))),
        );
    }

    public function asyncTravelResource(int $resourceId): array
    {
        return [
            'resource' => TravelResource::loadBy($resourceId),
        ];
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
            $out['city_name'] = $city->getName($this->travel->getLanguage());
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

        $out['languageCode'] = $this->travel->getLanguage()->getCode();
        $out['languageLabel'] = $this->travel->getLanguage()->getLabel();

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
                DateTimer::make('travel.date_from')
                    ->title('Date from'),
                DateTimer::make('travel.date_to')
                    ->enableTime(false)
                    ->format('Y-m-d')
                    ->title('Date to'),
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

            Button::make('save')
                ->class('mr-btn-success pull-right')
                ->method('saveTravel'),
        ]);
    }

    private function getRightTab(): Tabs
    {
        return Layout::tabs([
            'Photo'     => Layout::rows($this->getPhotoTab()),
            'Resources' => Layout::rows($this->getResourceTab()),
            'Points'    => Layout::rows($this->getTravelPointsLayout()),
            'Users'     => Layout::rows($this->getUITActiveListLayout()),
            'Invites'   => Layout::rows([
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
                //new InviteListLayout()
            ]),
        ]);
    }

    public function getResourceTab(): array
    {
        $list = $this->travelService->getResources($this->travel->id);

        $btns = [
            ModalToggle::make('link')
                ->class('mr-btn-success')
                ->modal('travel_resource_link')
                ->modalTitle('add link')
                ->method('addTravelLink', ['resourceId' => 0]),
            ModalToggle::make('File')
                ->class('mr-btn-success')
                ->modal('travel_resource_file')
                ->modalTitle('Add file')
                ->method('saveTravelFile', ['resourceId' => 0]),
            Button::make('delete all')
                ->class('mr-btn-danger')
                ->confirm('delete all resources?')
                ->method('deleteTravelResources'),
            ViewField::make('')->view('admin.raw')->class('')->value('Full size: ' . $this->travelService->getTravelResourcesSizeDisplay($this->user->id)),
        ];

        /** @var TravelResource $item */
        foreach ($list as &$item) {
            $item->linkAction = null;
            $rowBtns = [];
            $deleteResourceBtn = Button::make('delete')
                ->icon('bs.trash3')
                ->confirm('Delete resource?')
                ->method('deleteTravelResource', ['resourceId' => $item->id]);

            switch ($item->getType()) {
                case TravelResourceType::Link:
                    $item->linkAction = Link::make('')->icon('eye')->href($item->path)->target('_blank');
                    $rowBtns[] = ModalToggle::make('edit')
                        ->icon('pencil')
                        ->modal('travel_resource_link')
                        ->modalTitle('Edit link')
                        ->method('addTravelLink', ['resourceId' => $item->id]);
                    break;
                case TravelResourceType::File:
                    $item->title = $item->path;
                    $item->linkAction = Link::make('')->icon('download')->download($item->path)->target('_blank');
                    $rowBtns[] = ModalToggle::make('edit')
                        ->icon('pencil')
                        ->modal('travel_resource_file')
                        ->modalTitle('Edit file')
                        ->method('addTravelLink', ['resourceId' => $item->id]);
                    break;
                default:
                    $item->typeLabel = 'Unknown';
            }

            $rowBtns[] = $deleteResourceBtn;
            $item->btn = DropDown::make()->icon('options-vertical')->list($rowBtns)->render();
        }

        return [
            Group::make($btns)->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.travel.resources')->value($list),
        ];
    }

    public function addTravelLink(Request $request, int $resourceId): void
    {
        $input = $request->validate([
            'resource.title' => 'sometimes|nullable|string|max:255',
            'resource.path'  => 'required|url|max:255',
            'resource.sort'  => 'nullable|int',
        ])['resource'];

        $this->travelService->saveTravelResource(
            resourceId: $resourceId,
            travelId: $this->travel->id,
            type: TravelResourceType::Link,
            data: [
                'title'   => $input['title'] ?? null,
                'path'    => $input['path'],
                'sort'    => (int)$input['sort'],
                'user_id' => $this->user->id,
            ]
        );
    }

    public function saveTravelFile(Request $request, int $resourceId): void
    {
        $input = $request->validate([
            'resource.title' => 'sometimes|nullable|string|max:255',
            'resource.file'  => 'required|file',
            'resource.sort'  => 'nullable|int',
        ])['resource'];

        $uploadedFile = $request->file('resource.file');

        $this->travelService->saveTravelResource(
            resourceId: $resourceId,
            travelId: $this->travel->id,
            type: TravelResourceType::File,
            data: [
                'title'   => $input['title'] ?? null,
                'file'    => new UploadedFile($uploadedFile->getRealPath(), $uploadedFile->getClientOriginalName(), $uploadedFile->getClientMimeType(), null, true),
                'sort'    => (int)$input['sort'],
                'user_id' => $this->user->id,
            ]
        );
    }

    public function deleteTravelResource(int $resourceId): void
    {
        $this->travelService->deleteTravelResource($resourceId);
    }

    public function deleteTravelResources(): void
    {
        $this->travelService->deleteTravelResources($this->travel->id);
    }

    private function getTravelPointsLayout(): array
    {
        $list = $this->travelService->getTravelPoints($this->travel->id);

        $btns = [
            ModalToggle::make('add point')
                ->class('mr-btn-success')
                ->modal('point_edit_modal')
                ->modalTitle('Edit point')
                ->method('setPoint', ['pointId' => 0]),
            Button::make('delete all points')
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
                ModalToggle::make('edit')
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
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.travel.users')->value($list)
        ];
    }

    private function getPhotoTab(): array
    {
        $mediaList = $this->travelService->getTravelMediaList($this->travel->id());

        $photoTab = [
            Group::make([
                ModalToggle::make('Add photo')
                    ->class('mr-btn-success')
                    ->modal('upload_travel_photo')
                    ->modalTitle('Add photo')
                    ->method('saveTravelPhoto'),

                Button::make('Delete all')
                    ->method('deleteAllTravelPhotos')
                    ->novalidate()
                    ->class('mr-btn-danger')
                    ->canSee(count($mediaList) > 0)
                    ->confirm('Delete all photos?')
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