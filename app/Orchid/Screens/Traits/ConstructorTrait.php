<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Traits;

use App\Models\Constructor\Constructor;
use App\Models\Orchid\Attachment;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Orchid\Layouts\Constructor\ConstructorBlockEditLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemOutVideoEditLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemSlideEditLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemSliderEditLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemTextEditLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemVideoAddLayout;
use App\Orchid\Layouts\Constructor\ConstructorBlockItemVideoEditLayout;
use App\Services\Constructor\DTO\SlideDTO;
use App\Services\Constructor\Enum\ConstructorTypeEnum;
use App\Services\System\Enum\Language;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use stdClass;

trait ConstructorTrait
{
    public function getConstructorPopupLayout(): array
    {
        return [
            Layout::modal('edit_block_block', ConstructorBlockEditLayout::class)->async('asyncGetBlockBlock'),
            Layout::modal('edit_block_item_text', ConstructorBlockItemTextEditLayout::class)->async('asyncGetBlockItemText')->size(Modal::SIZE_XL),
            Layout::modal('edit_block_item_slider', ConstructorBlockItemSliderEditLayout::class)->async('asyncGetBlockItemSlider')->size(Modal::SIZE_LG),
            Layout::modal('add_block_item_video', ConstructorBlockItemVideoAddLayout::class)->size(Modal::SIZE_LG),
            Layout::modal('edit_block_item_video', ConstructorBlockItemVideoEditLayout::class)->async('asyncGetBlockItemVideo')->size(Modal::SIZE_LG),
            Layout::modal('edit_block_item_out_video', ConstructorBlockItemOutVideoEditLayout::class)->async('asyncGetBlockItemOutVideo')->size(Modal::SIZE_LG),
            Layout::modal('edit_block_item_slide', ConstructorBlockItemSlideEditLayout::class)->async('asyncGetBlockItemSlide')->size(Modal::SIZE_LG),
        ];
    }

    public function getConstructorLayout(object $object, ConstructorObjectTypeEnum $type, Language $language, bool $need_icon = true): array
    {
        $list = $this->constructorService->getConstructorBlocks($object->id(), $type, $language);
        $out[] = ViewField::make('')->view('admin.h6')->value('Конструктор');
        $actionBtn[] = ModalToggle::make('Добавить')->class('btn btn-sm')->type(Color::INFO())
            ->modal('edit_block_block')
            ->class('mr-btn-primary')
            ->modalTitle('Добавить блок')
            ->method('saveConstructorBlock', ['objectId' => $object->id(), 'objectType' => $type->value, 'constructorId' => 0]);

        count($list) && $actionBtn[] = Button::make('Удалить все')
            ->class('mr-btn-danger')
            ->method('deleteAllConstructorBlocks')->novalidate()
            ->confirm('Удалить все?')
            ->parameters(['objectId' => $object->id(), 'type' => $type->value, 'language' => $language->value]);

        $out[] = Group::make($actionBtn)->autoWidth();

        foreach ($list as $block) {
            $this->getBlockLayout($block, $type, $object, $out);
        }

        return $out;
    }

    public function getBlockLayout(Constructor $constructor, ConstructorObjectTypeEnum $type, object $object, array &$out): void
    {
        $out[] = ViewField::make('')->view('hr');
        $out[] = Group::make([
            ViewField::make('')->view('admin.h6')->value($constructor->getSort()),
            ViewField::make('')->view('admin.h6')->value($constructor->getTitle()),
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_block')
                ->modalTitle('Изменить блок')
                ->method('saveConstructorBlock', ['objectId' => $object->id(), 'objectType' => $type->value, 'constructorId' => $constructor->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteConstructorBlock')->novalidate()
                ->parameters(['objectId' => $object->id(), 'blockId' => $constructor->id()]),
        ])->autoWidth();

        $out[] = Group::make([
            ViewField::make('')->view('admin.h6')->value('Добавить: '),

            ModalToggle::make('текст')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_text')
                ->modalTitle('Добавить текстовый элемент')
                ->method('saveBlockItemText', ['constructorId' => $constructor->id(), 'itemId' => 0]),

            ModalToggle::make('слайдер')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_slider')
                ->modalTitle('Добавить слайдер')
                ->method('saveBlockItemSlider', ['constructorId' => $constructor->id(), 'itemId' => 0]),

            ModalToggle::make('видео файл')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('add_block_item_video')
                ->modalTitle('Добавить видео')
                ->method('saveBlockItemVideo', ['constructorId' => $constructor->id(), 'itemId' => 0]),

            ModalToggle::make('видео ссылка')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_out_video')
                ->modalTitle('Добавить видео ссылку')
                ->method('saveBlockItemOutVideo', ['blockId' => $constructor->id(), 'itemId' => 0]),

            Button::make('удалить все элементы блока')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockAllItems')->novalidate()
                ->parameters(['objectId' => $object->id(), 'blockId' => $constructor->id()]),
        ])->autoWidth();

        $list = $this->constructorService->getBlockItems($constructor->id());

        $rows = [];
        foreach ($list as $item) {
            $rows[] = $this->getItemLayout($item, $object);
        }

        $out[] = ViewField::make('')->view('admin.constructor.table')->value($rows);
    }

    public function getItemLayout($item, object $object): ?View
    {
        return match ($item->type) {
            ConstructorTypeEnum::Text->value => $this->getTextItemLayout($item, $object),
            ConstructorTypeEnum::Video->value => $this->getVideoItemLayout($item, $object),
            ConstructorTypeEnum::OutVideo->value => $this->getOutVideoItemLayout($item, $object),
            ConstructorTypeEnum::Slider->value => $this->getSliderItemLayout($item, $object),
            default => null,
        };
    }

    public function getSliderItemLayout(stdClass $item, object $object): View
    {
        $itemSlider = $this->constructorService->getBlockItemById($item->id, ConstructorTypeEnum::Slider);

        $itemSlider->btn = Group::make([
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_item_slider')
                ->modalTitle('Изменить элемент')
                ->method('saveBlockItemSlider', ['constructorId' => $itemSlider->constructor_id, 'itemId' => $itemSlider->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem', ['objectId' => $object->id(), 'itemId' => $itemSlider->id, 'type' => ConstructorTypeEnum::Slider->value]),
        ])->autoWidth()->render();

        foreach ($itemSlider->images as $slide) {
            $slide->btn = Group::make([
                ViewField::make('')->view('admin.raw')->value('Сорт. ' . $slide->getSort())->render(),
                ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                    ->modal('edit_block_item_slide')
                    ->modalTitle('Изменить элемент')
                    ->method('saveBlockItemSlide', ['constructorId' => $itemSlider->constructor_id, 'sliderId' => $itemSlider->id(), 'itemId' => $slide->id]),

                Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                    ->method('deleteBlockItemSlide')->novalidate()
                    ->parameters(['objectId' => $object->id(), 'slide_id' => $slide->id]),
            ])->autoWidth()->render();
        }
        return ViewField::make('')->view('admin.constructor.item_slider')->value($itemSlider)->render();
    }

    public function getTextItemLayout(stdClass $item, object $object): View
    {
        $itemText = $this->constructorService->getBlockItemById($item->id, ConstructorTypeEnum::Text);

        $itemText->btn = Group::make([
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_item_text')
                ->modalTitle('Изменить элемент')
                ->method('saveBlockItemText', ['constructorId' => $itemText->constructor_id, 'itemId' => $itemText->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['objectId' => $object->id(), 'itemId' => $itemText->id(), 'type' => ConstructorTypeEnum::Text->value]),
        ])->autoWidth()->render();

        return ViewField::make('')->view('admin.constructor.item_text')->value($itemText)->render();
    }

    public function getVideoItemLayout(stdClass $item, object $object): View
    {
        $itemText = $this->constructorService->getBlockItemById($item->id, ConstructorTypeEnum::Video);

        $itemText->btn = Group::make([
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_item_video')
                ->modalTitle('Изменить элемент')
                ->method('saveBlockItemVideo', ['constructorId' => $itemText->constructor_id, 'itemId' => $itemText->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['objectId' => $object->id(), 'itemId' => $itemText->id(), 'type' => ConstructorTypeEnum::Video->value]),
        ])->autoWidth()->render();

        return ViewField::make('')->view('admin.constructor.item_video')->value($itemText)->render();
    }

    public function getOutVideoItemLayout(stdClass $item, object $object): View
    {
        $itemOutVideo = $this->constructorService->getBlockItemById($item->id, ConstructorTypeEnum::OutVideo);
        if (Str::startsWith($itemOutVideo->url, 'https://www.youtube.com/watch?v=')) {
            $itemOutVideo->url = str_replace('https://www.youtube.com/watch?v=', 'https://www.youtube.com/embed/', $itemOutVideo->url);
        }
        $itemOutVideo->btn = Group::make([
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_item_out_video')
                ->modalTitle('Изменить элемент')
                ->method('saveBlockItemOutVideo', ['constructorId' => $itemOutVideo->constructor_id, 'itemId' => $itemOutVideo->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['objectId' => $object->id(), 'itemId' => $itemOutVideo->id(), 'type' => ConstructorTypeEnum::OutVideo->value]),
        ])->autoWidth()->render();

        return ViewField::make('')->view('admin.constructor.item_out_video')->value($itemOutVideo)->render();
    }

    public function deleteBlockAllItems(int $objectId, int $blockId): void
    {
        $this->constructorService->deleteBlockAllItem($objectId, $blockId);
    }

    public function deleteBlockItemSlide(int $objectId, int $slide_id): void
    {
        $this->constructorService->deleteBlockItemSlide($slide_id, $objectId);
    }

    public function deleteBlockItem(int $objectId, int $itemId, string $type): void
    {
        $this->constructorService->deleteBlockItem($itemId, ConstructorTypeEnum::from($type), $objectId);
    }

    public function asyncGetBlockItemText(int $itemId = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($itemId, ConstructorTypeEnum::Text)];
    }

    public function asyncGetBlockItemVideo(int $itemId = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($itemId, ConstructorTypeEnum::Video)];
    }

    public function asyncGetBlockItemSlide(int $itemId): array
    {
        return ['item' => $this->constructorService->getBlockItemSlideById($itemId)];
    }

    public function asyncGetBlockItemSlider(int $itemId = 0): array
    {
        return [
            'item' => $this->constructorService->getBlockItemById($itemId, ConstructorTypeEnum::Slider)
        ];
    }

    public function asyncGetBlockItemOutVideo(int $itemId = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($itemId, ConstructorTypeEnum::OutVideo)];
    }

    public function saveBlockItemSlide(Request $request, int $constructorId, int $sliderId, int $itemId): void
    {
        $input = Validator::make($request->all(), [
            'item.display_name' => 'nullable|string|max:255',
            'item.alt'          => 'nullable|string|max:255',
            'item.sort'         => 'nullable|integer|min:0|max:999',
        ])->validate()['item'];

        $dto = new SlideDTO(
            constructorId: $constructorId,
            sliderId: $sliderId,
            slideId: $itemId,
            image: null,
            displayName: $input['display_name'],
            sort: (int)$input['sort'],
            alt: $input['alt'],
        );

        $this->constructorService->saveBlockItemSlide($dto);
    }

    public function saveBlockItemSlider(Request $request, int $constructorId, int $itemId): void
    {
        $input = Validator::make($request->all(), [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string|max:10000',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ])->validate()['item'];

        $sliderId = $this->constructorService->saveSlider($itemId, [
            'constructor_id' => $constructorId,
            'title'          => $input['title'],
            'description'    => $input['description'],
            'sort'           => (int)$input['sort'],
        ]);

        // Add slides
        foreach ($request->get('item')['images'] ?? [] as $idAtt) {
            $attachment = Attachment::loadByOrDie((int)$idAtt);
            $path = Storage::path($attachment->getFullPath());

            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }

            $dto = new SlideDTO(
                constructorId: $constructorId,
                sliderId: $sliderId,
                slideId: $itemId,
                image: new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true),
                displayName: $attachment->getDescription(),
                sort: $attachment->getSort(),
                alt: $attachment->getAlt(),
            );

            $this->constructorService->saveBlockItemSlide($dto);
        }

        Attachment::whereIn('id', $request->get('item')['images'] ?? [])->delete();
    }

    public function saveBlockItemText(Request $request, int $itemId, int $constructorId): void
    {
        $input = $request->validate([
            'item.title' => 'nullable|string|max:255',
            'item.text'  => 'nullable|string',
            'item.sort'  => 'nullable|integer|min:0|max:999',
        ])['item'];

        $input['constructor_id'] = $constructorId;
        $input['sort'] = (int)$input['sort'];

        $this->constructorService->saveBlockItemText($itemId, $input);
        Toast::info('Элемент сохранён');
    }

    public function saveBlockItemVideo(Request $request, int $constructorId, int $itemId): void
    {
        $rules = [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ];

        if (!$itemId) { // For edit only
            $rules['item.file'] = 'required|file';
        }

        $input = $request->validate($rules)['item'];

        $input['constructor_id'] = $constructorId;
        $input['sort'] = (int)$input['sort'];

        $this->constructorService->saveBlockItemVideo($itemId, $input);

        Toast::info('Элемент сохранён');
    }

    public function saveBlockItemOutVideo(Request $request, int $blockId, int $itemId): void
    {
        $rules = [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string',
            'item.url'         => 'required|string|max:10000',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ];

        $input = $request->validate($rules)['item'];

        $input['constructor_id'] = $blockId;
        $input['sort'] = (int)$input['sort'];

        $this->constructorService->saveBlockItemOutVideo($itemId, $input);
        Toast::info('Элемент сохранен');
    }

    public function asyncGetBlockBlock(int $constructorId = 0): array
    {
        return [
            'block' => $this->constructorService->getBlockById($constructorId),
        ];
    }

    public function saveConstructorBlock(Request $request, int $objectId, int $objectType, int $constructorId): void
    {
        $input = $request->validate([
            'block.title' => 'required|string|max:255',
            'block.sort'  => 'nullable|integer|min:0|max:999',
        ])['block'];

        $input['object_id'] = $objectId;
        $input['sort'] = (int)$input['sort'];
        $input['object_type'] = ConstructorObjectTypeEnum::from($objectType)->value;

        $this->constructorService->saveConstructorBlock($constructorId, $input);
        Toast::info('Конструктор сохранён');
    }

    public function deleteConstructorBlock(int $objectId, int $blockId): void
    {
        $this->constructorService->deleteConstructorBlock($objectId, $blockId);
    }

    public function deleteAllConstructorBlocks(int $objectId, int $type, int $language): void
    {
        $type = ConstructorObjectTypeEnum::from($type);
        $language = Language::from($language);
        $this->constructorService->deleteAllConstructorBlocks($objectId, $type, $language);
        Toast::info('Все функции удалены');
    }
}