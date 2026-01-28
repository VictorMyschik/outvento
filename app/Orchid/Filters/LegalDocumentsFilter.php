<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\Other\LegalDocument;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Other\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class LegalDocumentsFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'active',
        'type',
        'language',
        'text',
        'published_at',
        'created_at',
        'updated_at',
    ];

    public static function runQuery(): Builder
    {
        return LegalDocument::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!is_null($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        if (!empty($input['type'])) {
            $builder->where('type', $input['type']);
        }

        if (!empty($input['text'])) {
            $builder->where('text', 'like', '%' . $input['text'] . '%');
        }

        if (!empty($input['language'])) {
            $builder->where('language', $input['language']);
        }

        if (!empty($input['published_at'])) {
            $builder->whereDate('published_at', $input['published_at']);
        }

        if (!empty($input['created_at'])) {
            $builder->whereDate('created_at', $input['created_at']);
        }

        if (!empty($input['updated_at'])) {
            $builder->whereDate('updated_at', $input['updated_at']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('id')->type('number')->value($input['id'])->title('ID'),
                Select::make('type')
                    ->options(LegalDocumentType::getSelectList())
                    ->value($input['type'])
                    ->empty('Все')
                    ->title('Тип документа'),
                Select::make('active')
                    ->options([null => 'Все', 1 => 'Активные', 0 => 'Не активные'])
                    ->value($input['active'])
                    ->title('Активно'),
                Input::make('text')->value($input['text'])->title('Text'),
            ]),

            Group::make([
                Select::make('language')
                    ->options(Language::getSelectList())
                    ->value($input['language'])
                    ->empty('Все')
                    ->title('Язык'),
                Input::make('published_at')->type('date')->value($input['published_at'])->title('Дата публикации'),
                Input::make('created_at')->type('date')->value($input['created_at'])->title('Дата создания'),
                Input::make('updated_at')->type('date')->value($input['updated_at'])->title('Дата обновления'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}