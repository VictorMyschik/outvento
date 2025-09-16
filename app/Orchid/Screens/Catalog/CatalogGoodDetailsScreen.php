<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Catalog;

use App\Models\Catalog\Onliner\OnCatalogGood;
use App\Models\Catalog\Onliner\OnManufacturer;
use App\Models\Orchid\Attachment;
use App\Orchid\Layouts\Catalog\GoodUploadEditLayout;
use App\Orchid\Layouts\Lego\ActionDeleteModelLayout;
use App\Orchid\Layouts\Lego\InfoModalLayout;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use App\Services\Catalog\Enum\CatalogTypeEnum;
use App\Services\Catalog\Onliner\ImportOnlinerService;
use App\Services\Catalog\Onliner\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CatalogGoodDetailsScreen extends Screen
{
    public function __construct(
        private readonly CatalogService       $service,
        private readonly ImportOnlinerService $importService,
    ) {}

    public ?OnCatalogGood $good = null;

    public function name(): ?string
    {
        return $this->good->getName();
    }

    public function description(): ?string
    {
        return View('admin.created_updated', ['model' => $this->good])->toHtml();
    }

    public function query(int $id): iterable
    {
        return [
            'good' => $this->good = $this->service->getGoodById($id),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')->class('mr-btn-success')->method('saveGood')->parameters(['id' => $this->good->id()]),
            Link::make('Назад')->icon('arrow-up')->route('goods.list'),
        ];
    }

    public function layout(): iterable
    {
        $out = [];

        $out[] = Layout::split([
            $this->getBaseLayout(),
            $this->getAdditionalLayout(),
        ]);

        $out[] = Layout::rows([
            $this->getImageBtnLayout(),
            $this->getImageLayout()
        ]);

        $out[] = Layout::rows($this->getAttributeLayout());
        $out[] = Layout::rows([
            ActionDeleteModelLayout::getActionButtons('Удалить товар', 'deleteGood', ['id' => $this->good->id()]),
        ]);

        $out[] = Layout::modal('view_good', InfoModalLayout::class)->async('asyncGetGood')->size(Modal::SIZE_XL);
        $out[] = Layout::modal('upload_good_photo', GoodUploadEditLayout::class)->async('asyncGetGoodPhoto');

        return $out;
    }

    private function getAttributeLayout(): array
    {
        $list = $this->service->getGoodAttributes($this->good->id());

        return [
            ViewField::make('')->view('admin.onliner.good_attributes')->value($list),
        ];
    }

    private function getImageLayout(): ViewField
    {
        $images = $this->service->getGoodImages($this->good->id());

        foreach ($images as $image) {
            $image->btn = Group::make([
                Button::make('удалить')->class('mr-btn-danger')->icon('trash')->confirm('Удалить?')
                    ->method('deleteImage')->novalidate()
                    ->parameters(['image_id' => $image->id()]),
            ])->autoWidth()->render();
        }

        return ViewField::make('')->view('admin.good_images')->value($images);
    }

    private function getBaseLayout(): Rows
    {
        return Layout::rows([
            Group::make([
                Switcher::make('good.active')->sendTrueOrFalse()->title('Активно'),
                Relation::make('good.parent_good_id')->title('Родительский товар')->allowEmpty()->fromModel(OnCatalogGood::class, 'string_id', 'string_id'),
            ]),
            Input::make('good.name')->required()->title('Наименование'),
            Input::make('good.prefix')->title('Префикс'),
            Input::make('good.short_info')->title('Краткая информация'),
            TextArea::make('good.description')->name('good.description')->rows(5)->title('Описание'),
        ]);
    }

    private function getAdditionalLayout(): Rows
    {
        return Layout::rows([
            Relation::make('good.manufacturer_id')->title('Производитель')->fromModel(OnManufacturer::class, 'name'),
            Switcher::make('good.is_certification')->sendTrueOrFalse()->title('Требование сертификации')->horizontal(),
            Group::make([
                Label::make('')->title('Json данные при импорте'),
                ModalToggle::make('Json')
                    ->modalTitle('Json')
                    ->modal('view_good')
                    ->parameters(['id' => $this->good->id()]),
            ])->autoWidth(),
            Link::make('link')->title('Ссылка на страницу Onliner')->horizontal()->icon('link')->target('_blank')->href($this->good->link),
            Input::make('good.string_id')->title('Строковый ID')->horizontal(),
            Input::make('good.int_id')->title('Числовой ID')->horizontal(),
            Input::make('good.vendor_code')->title('Наш артикул')->horizontal(),
        ]);
    }

    private function getImageBtnLayout(): Group
    {
        return Group::make([
            ModalToggle::make('Загрузить фото')
                ->class('mr-btn-success')
                ->modal('upload_good_photo')
                ->modalTitle('Загрузить фото')
                ->method('saveGoodPhoto', ['good_id' => $this->good?->id(), 'catalog_image_id' => 0]),

            Button::make('Перезагрузить фото')
                ->method('reUploadGoodPhotos')
                ->novalidate()
                ->class('mr-btn-success')
                ->confirm('Вы уверены, что хотите перезагрузить все картинки с сайта Onliner?'),

            Button::make('Удалить все фото')
                ->method('deleteAllGoodPhoto')
                ->novalidate()
                ->class('mr-btn-danger')
                ->confirm('Вы уверены, что хотите удалить все фото?')
                ->parameters(['good_id' => $this->good?->id()]),
        ])->autoWidth();
    }

    public function asyncGetGoodPhoto(int $catalog_image_id = 0): array
    {
        return [
            'good'  => $this->good,
            'image' => $catalog_image_id ? $this->service->getGoodImageById($catalog_image_id) : null,
        ];
    }

    public function asyncGetGood(int $id = 0): array
    {
        return [
            'body' => OnCatalogGood::loadByOrDie($id)->getSL(),
        ];
    }

    public function reUploadGoodPhotos(): void
    {
        $this->importService->reloadGoodImages($this->good);
    }

    public function saveGood(Request $request, int $id): void
    {
        $input = Validator::make($request->all(), [
            'good.active'           => 'boolean',
            'good.name'             => 'required',
            'good.prefix'           => 'nullable',
            'good.short_info'       => 'nullable',
            'good.description'      => 'nullable',
            'good.manufacturer_id'  => 'nullable|integer',
            'good.is_certification' => 'boolean',
            'good.int_id'           => 'nullable|integer',
            'good.string_id'        => 'nullable|string',
            'good.link'             => 'nullable|string',
            'good.vendor_code'      => 'nullable|string',
            'good.parent_good_id'   => 'nullable|string|exists:on_catalog_goods,string_id|not_in:' . $this->good->getParentGoodId(),
        ])->validate()['good'];

        $this->service->saveGood($id, $input);
    }

    public function saveGoodPhoto(Request $request, int $good_id): void
    {
        $imageAttachIds = $request->get('images', []);
        $good = OnCatalogGood::loadByOrDie($good_id);

        foreach ($imageAttachIds as $imageAttachId) {
            $attachment = Attachment::loadByOrDie((int)$imageAttachId);
            $this->service->saveGoodImage($good, $attachment, CatalogImageTypeEnum::PHOTO);
        }
    }

    public function deleteImage(int $image_id): void
    {
        $this->service->deleteImage($image_id);
    }

    public function deleteGood(int $id): RedirectResponse
    {
        $this->service->deleteGood($id);

        return redirect()->route('goods.list');
    }

    public function deleteAllGoodPhoto(int $good_id): void
    {
        $this->service->deleteAllGoodPhoto($good_id);
    }
}
