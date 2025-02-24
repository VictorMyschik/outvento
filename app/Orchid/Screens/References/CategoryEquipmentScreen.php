<?php

namespace App\Orchid\Screens\References;

use App\Models\Reference\CategoryEquipment;
use App\Orchid\Layouts\References\CategoryEquipmentEditLayout;
use App\Orchid\Layouts\References\CategoryEquipmentListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CategoryEquipmentScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'list' => CategoryEquipment::filters([])->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return 'Категории вещей';
    }

    public function description(): ?string
    {
        return 'Справочник категорий вещей, снаряжения и т.д.';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('category_equipment')
                ->modalTitle('Create New Category Equipment')
                ->method('saveCategoryEquipment')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            CategoryEquipmentListLayout::class,
            Layout::modal('category_equipment', CategoryEquipmentEditLayout::class)->async('asyncGetCategoryEquipment'),
        ];
    }

    public function asyncGetCategoryEquipment(int $id = 0): array
    {
        return [
            'category-equipment' => CategoryEquipment::loadBy($id) ?: new CategoryEquipment()
        ];
    }

    public function saveCategoryEquipment(Request $request): void
    {
        $data = $request->validate([
            'category-equipment.name'        => 'required|string',
            'category-equipment.description' => 'nullable|string',
        ])['category-equipment'];

        CategoryEquipment::updateOrCreate(
            ['id' => (int)$request->get('id')],
            $data
        );

        Toast::info('Category Equipment was saved');
    }

    public function remove(int $id): void
    {
        try {
            CategoryEquipment::loadBy($id)?->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
