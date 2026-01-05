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
use App\Repositories\Constructor\Storage\Enum\StorageFileTypeEnum;
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
    public function getPopupLayout(array &$out): void
    {
        $out[] = Layout::modal('edit_block_block', ConstructorBlockEditLayout::class)->async('asyncGetBlockBlock');
        $out[] = Layout::modal('edit_block_item_text', ConstructorBlockItemTextEditLayout::class)->async('asyncGetBlockItemText')->size(Modal::SIZE_XL);
        $out[] = Layout::modal('edit_block_item_slider', ConstructorBlockItemSliderEditLayout::class)->async('asyncGetBlockItemSlider')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('add_block_item_video', ConstructorBlockItemVideoAddLayout::class)->size(Modal::SIZE_LG);
        $out[] = Layout::modal('edit_block_item_video', ConstructorBlockItemVideoEditLayout::class)->async('asyncGetBlockItemVideo')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('edit_block_item_out_video', ConstructorBlockItemOutVideoEditLayout::class)->async('asyncGetBlockItemOutVideo')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('edit_block_item_slide', ConstructorBlockItemSlideEditLayout::class)->async('asyncGetBlockItemSlide')->size(Modal::SIZE_LG);
    }

    public function getConstructorLayout(object $object, ConstructorObjectTypeEnum $type, Language $language, bool $need_icon = true): array
    {
        $list = $this->constructorService->getConstructorBlocks($object->id(), $type, $language);
        $out[] = ViewField::make('')->view('admin.h6')->value('Конструктор');
        $actionBtn[] = ModalToggle::make('Добавить')->class('btn btn-sm')->type(Color::INFO())
            ->modal('edit_block_block')
            ->class('mr-btn-primary')
            ->modalTitle('Добавить блок')
            ->method('saveConstructorBlock', ['object_id' => $object->id(), 'type' => $type->value, 'block_id' => 0, 'language' => $language->value, 'need_icon' => (int)$need_icon]);

        count($list) && $actionBtn[] = Button::make('Удалить все')
            ->class('mr-btn-danger')
            ->method('deleteAllConstructorBlocks')->novalidate()
            ->confirm('Удалить все?')
            ->parameters(['object_id' => $object->id(), 'type' => $type->value, 'language' => $language->value]);

        $out[] = Group::make($actionBtn)->autoWidth();

        foreach ($list as $block) {
            $this->getBlockLayout($block, $type, $object, $language, $out, $need_icon);
        }

        return $out;
    }

    public function getBlockLayout(Constructor $block, ConstructorObjectTypeEnum $type, object $object, Language $language, array &$out, bool $need_icon = true): void
    {
        $out[] = ViewField::make('')->view('hr');
        $out[] = Group::make([
            ViewField::make('')->view('admin.h6')->value($block->getSort()),
            ViewField::make('')->view('admin.constructor.bloc_icon')->value($block->getIcon()),
            ViewField::make('')->view('admin.h6')->value($block->getTitle()),
            ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                ->modal('edit_block_block')
                ->modalTitle('Изменить блок')
                ->method('saveConstructorBlock', ['object_id' => $object->id(), 'type' => $type->value, 'block_id' => $block->id(), 'language' => $language->value, 'need_icon' => (int)$need_icon]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteConstructorBlock')->novalidate()
                ->parameters(['object_id' => $object->id(), 'block_id' => $block->id()]),
        ])->autoWidth();

        $out[] = Group::make([
            ViewField::make('')->view('admin.h6')->value('Добавить: '),

            ModalToggle::make('текст')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_text')
                ->modalTitle('Добавить текстовый элемент')
                ->method('saveBlockItemText', ['item_id' => 0, 'object_id' => $object->id(), 'block_id' => $block->id()]),

            ModalToggle::make('слайдер')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_slider')
                ->modalTitle('Добавить слайдер')
                ->method('saveBlockItemSlider', ['object_id' => $object->id(), 'block_id' => $block->id(), 'item_id' => 0]),

            ModalToggle::make('видео файл')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('add_block_item_video')
                ->modalTitle('Добавить видео')
                ->method('saveBlockItemVideo', ['object_id' => $object->id(), 'block_id' => $block->id(), 'item_id' => 0]),

            ModalToggle::make('видео ссылка')->class('btn btn-sm')->icon('plus')->class('btn btn-sm fa mx-2')->type(Color::LIGHT())
                ->modal('edit_block_item_out_video')
                ->modalTitle('Добавить видео')
                ->method('saveBlockItemOutVideo', ['object_id' => $object->id(), 'block_id' => $block->id(), 'item_id' => 0]),

            Button::make('удалить все элементы блока')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockAllItems')->novalidate()
                ->parameters(['object_id' => $object->id(), 'block_id' => $block->id()]),
        ])->autoWidth();

        $list = $this->constructorService->getBlockItems($block->id());

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
                ->method('saveBlockItemSlider', ['object_id' => $object->id(), 'block_id' => $itemSlider->constructor_id, 'item_id' => $itemSlider->id()]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem', ['object_id' => $object->id(), 'item_id' => $itemSlider->id, 'type' => ConstructorTypeEnum::Slider->value]),
        ])->autoWidth()->render();

        foreach ($itemSlider->images as $image) {
            $image->btn = Group::make([
                ViewField::make('')->view('admin.raw')->value('Сорт. ' . $image->getSort())->render(),
                ModalToggle::make('изменить')->class('btn btn-sm')->icon('pencil')
                    ->modal('edit_block_item_slide')
                    ->modalTitle('Изменить элемент')
                    ->method('saveBlockItemSlide', ['slide_id' => $image->id, 'object_id' => $object->id()]),

                Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                    ->method('deleteBlockItemSlide')->novalidate()
                    ->parameters(['object_id' => $object->id(), 'slide_id' => $image->id]),
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
                ->method('saveBlockItemText', ['item_id' => $itemText->id(), 'object_id' => $object->id(), 'block_id' => $itemText->constructor_id]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['object_id' => $object->id(), 'item_id' => $itemText->id(), 'type' => ConstructorTypeEnum::Text->value]),
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
                ->method('saveBlockItemVideo', ['item_id' => $itemText->id(), 'object_id' => $object->id(), 'block_id' => $itemText->constructor_id]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['object_id' => $object->id(), 'item_id' => $itemText->id(), 'type' => ConstructorTypeEnum::Video->value]),
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
                ->method('saveBlockItemOutVideo', ['item_id' => $itemOutVideo->id(), 'object_id' => $object->id(), 'block_id' => $itemOutVideo->constructor_id]),
            Button::make('удалить')->class('btn btn-sm')->icon('trash')->confirm('Удалить?')
                ->method('deleteBlockItem')->novalidate()
                ->parameters(['object_id' => $object->id(), 'item_id' => $itemOutVideo->id(), 'type' => ConstructorTypeEnum::OutVideo->value]),
        ])->autoWidth()->render();

        return ViewField::make('')->view('admin.constructor.item_out_video')->value($itemOutVideo)->render();
    }

    public function deleteBlockAllItems(int $object_id, int $block_id): void
    {
        $this->constructorService->deleteBlockAllItem($object_id, $block_id);
    }

    public function deleteBlockItemSlide(int $object_id, int $slide_id): void
    {
        $this->constructorService->deleteBlockItemSlide($slide_id, $object_id);
    }

    public function deleteBlockItem(int $object_id, int $item_id, string $type): void
    {
        $this->constructorService->deleteBlockItem($item_id, ConstructorTypeEnum::from($type), $object_id);
    }

    public function asyncGetBlockItemText(int $item_id = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($item_id, ConstructorTypeEnum::Text)];
    }

    public function asyncGetBlockItemVideo(int $item_id = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($item_id, ConstructorTypeEnum::Video)];
    }

    public function asyncGetBlockItemSlide(int $slide_id): array
    {
        return ['item' => $this->constructorService->getBlockItemSlideById($slide_id)];
    }

    public function asyncGetBlockItemSlider(int $item_id = 0): array
    {
        return [
            'item' => $this->constructorService->getBlockItemById($item_id, ConstructorTypeEnum::Slider)
        ];
    }

    public function asyncGetBlockItemOutVideo(int $item_id = 0): array
    {
        return ['item' => $this->constructorService->getBlockItemById($item_id, ConstructorTypeEnum::OutVideo)];
    }

    public function saveBlockItemSlide(Request $request, int $object_id, int $slide_id): void
    {
        $input = Validator::make($request->all(), [
            'item.display_name' => 'nullable|string|max:255',
            'item.alt'          => 'nullable|string|max:255',
            'item.sort'         => 'nullable|integer|min:0|max:999',
        ])->validate()['item'];

        $input['sort'] = (int)$input['sort'];
        $this->constructorService->saveBlockItemSlide($slide_id, $input);
    }

    public function saveBlockItemSlider(Request $request, int $object_id, int $block_id, int $item_id): void
    {
        $input = Validator::make($request->all(), [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string|max:10000',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ])->validate()['item'];

        $sliderId = $this->constructorService->saveSlider($item_id, [
            'constructor_id' => $block_id,
            'title'          => $input['title'],
            'description'    => $input['description'],
            'sort'           => (int)$input['sort'],
        ]);

        // Add slides
        $dtos = [];
        foreach ($request->get('item')['images'] ?? [] as $idAtt) {
            $attachment = Attachment::loadByOrDie((int)$idAtt);
            $path = Storage::path($attachment->getFullPath());

            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }

            $dtos[] = new SlideDTO(
                type: StorageFileTypeEnum::SlideImage,
                image: new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true),
                slider_id: $sliderId,
                display_name: $attachment->getDescription(),
                sort: $attachment->getSort(),
                alt: $attachment->getAlt(),
                user: $request->user()
            );
        }

        $this->constructorService->saveFiles($sliderId, StorageFileTypeEnum::SlideImage, $dtos);

        Attachment::whereIn('id', $request->get('item')['images'] ?? [])->delete();
    }

    public function saveBlockItemText(Request $request, int $item_id, int $object_id, int $block_id): void
    {
        $input = $request->validate([
            'item.title' => 'nullable|string|max:255',
            'item.text'  => 'nullable|string',
            'item.sort'  => 'nullable|integer|min:0|max:999',
        ])['item'];

        $input['constructor_id'] = $block_id;
        $input['sort'] = (int)$input['sort'];

        $this->constructorService->saveBlockItemText($item_id, $input);
        Toast::info('Элемент сохранён');
    }

    public function saveBlockItemVideo(Request $request, int $block_id, int $object_id, int $item_id): void
    {
        $rules = [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ];

        if (!$item_id) {
            $rules['item.file'] = 'required|file';
        }

        $input = $request->validate($rules)['item'];

        $input['constructor_id'] = $block_id;
        $input['sort'] = (int)$input['sort'];

        if (!$item_id) { // new
            $input['file_id'] = $this->constructorService->saveFile($input['file'], StorageFileTypeEnum::Video, $request->user());
            unset($input['file']);
        }
        $this->constructorService->saveBlockItemVideo($item_id, $input);
        Toast::info('Элемент сохранен');
    }

    public function saveBlockItemOutVideo(Request $request, int $block_id, int $object_id, int $item_id): void
    {
        $rules = [
            'item.title'       => 'nullable|string|max:255',
            'item.description' => 'nullable|string',
            'item.url'         => 'required|string|max:10000',
            'item.sort'        => 'nullable|integer|min:0|max:999',
        ];

        $input = $request->validate($rules)['item'];

        $input['constructor_id'] = $block_id;
        $input['sort'] = (int)$input['sort'];

        $this->constructorService->saveBlockItemOutVideo($item_id, $input);
        Toast::info('Элемент сохранен');
    }

    public function asyncGetBlockBlock(int $block_id = 0, int $need_icon = 0): array
    {
        return [
            'block'     => $this->constructorService->getBlockById($block_id),
            'need_icon' => (bool)$need_icon,
        ];
    }

    public function saveConstructorBlock(Request $request, int $type, int $object_id, int $block_id, int $language): void
    {
        $language = Language::from($language);
        $input = $request->validate([
            'block.title' => 'required|string|max:255',
            'block.sort'  => 'nullable|integer|min:0|max:999',
        ])['block'];

        $input['object_id'] = $object_id;
        $input['language'] = $language->value;
        $input['sort'] = (int)$input['sort'];
        $input['type'] = ConstructorObjectTypeEnum::from($type)->value;
        $input['icon'] = $request->get('block')['icon'][0] ?? null;

        $this->constructorService->saveConstructorBlock($block_id, $input);
        Toast::info('Функция сохранена');
    }

    public function deleteConstructorBlock(int $object_id, int $block_id): void
    {
        $this->constructorService->deleteConstructorBlock($object_id, $block_id);
    }

    public function deleteAllConstructorBlocks(int $object_id, int $type, int $language): void
    {
        $type = ConstructorObjectTypeEnum::from($type);
        $language = Language::from($language);
        $this->constructorService->deleteAllConstructorBlocks($object_id, $type, $language);
        Toast::info('Все функции удалены');
    }

    public function deleteBlockIcon(int $block_id): void
    {
        $this->constructorService->deleteBlockIcon($block_id);
    }
}