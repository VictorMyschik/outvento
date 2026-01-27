<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\TermsAndConditions;

use App\Models\Other\TermsAndCondition;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TermsAndConditionsListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->render(fn(TermsAndCondition $term) => Link::make((string)$term->id())->route('other.terms.and.conditions.edit', ['id' => $term->id])->stretched())->sort(),
            TD::make('active', 'Активно')->active()->sort(),
            TD::make('language', 'Язык')->render(fn(TermsAndCondition $term) => $term->getLanguage()->getLabel())->sort(),
            TD::make('published_at', 'Дата публикации')->render(fn(TermsAndCondition $term) => $term->published_at?->format('d.m.Y'))->sort(),
            TD::make('created_at', 'Создано')->render(fn(TermsAndCondition $term) => $term->created_at->format('d.m.Y'))->sort(),
            TD::make('updated_at', 'Обновлено')->render(fn(TermsAndCondition $term) => $term->updated_at?->format('d.m.Y h:i'))->sort(),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}
