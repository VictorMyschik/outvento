<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\Wishlist\Wishlist;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class WishlistFilter extends Filter
{
    public const array FIELDS = [
        'category',
        'subcategory',
        'title',
        'url',
        'price',
        'currency',
        'userId',
        'createdAt',
        'updatedAt',
        'archivedAt',
    ];

    public static function runQuery(): Builder
    {
        return Wishlist::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['category'])) {
            $builder->where('category', $input['category']);
        }

        if (!empty($input['title'])) {
            $builder->where('title', $input['title']);
        }

        if (!empty($input['url'])) {
            $builder->whereRaw('url LIKE ?', ['%' . $input['url'] . '%']);
        }

        if (!empty($input['price'])) {
            $builder->where('price', (float)$input['price']);
        }

        if (!empty($input['userId'])) {
            $builder->where('user_id', (int)$input['userId']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', $input['createdAt']);
        }
        if (!empty($input['updatedAt'])) {
            $builder->whereDate('updated_at', $input['updatedAt']);
        }

        if (!empty($input['archivedAt'])) {
            $builder->whereDate('archived_at', $input['archivedAt']);
        }


        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Relation::make('category')
                    ->value($input['category'])
                    ->fromModel(Wishlist::class, 'category', 'category'),
                Input::make('title')->value($input['title'])->title('title'),
                Input::make('url')->value($input['url'])->title('url'),
            ]),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
