<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Wishlist;

use App\Models\Wishlist\Wishlist;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class WishlistListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('category', 'Category')->sort(),
            TD::make('subcategory', 'Subcategory')->sort(),
            TD::make('title', 'Title')->sort(),
            TD::make('url', 'URL')->render(function ($wishlist) {
                return '<a href="' . $wishlist->url . '" target="_blank" title="Open in new tab">link</a>';
            })->sort(),
            TD::make('price', 'Price')->sort(),
            TD::make('currency', 'Currency')->sort(),
            TD::make('user_id', 'User ID')->sort(),
            TD::make('created_at', 'Created At')->sort(),
            TD::make('updated_at', 'Updated At')->sort(),
            TD::make('archived_at', 'Archived At')->sort(),

            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Wishlist $wishlist) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('wishlist_modal')
                            ->modalTitle('Edit wishlist id ' . $wishlist->id)
                            ->method('saveWishlist')
                            ->asyncParameters(['id' => $wishlist->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete wishlist id ' . $wishlist->id . '?')
                            ->method('remove', ['id' => $wishlist->id]),
                    ])),
        ];
    }
}