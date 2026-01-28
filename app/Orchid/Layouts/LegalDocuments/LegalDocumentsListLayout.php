<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\LegalDocuments;

use App\Models\Other\LegalDocument;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class LegalDocumentsListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->render(fn(LegalDocument $term) => Link::make((string)$term->id())->route('legal.documents.edit', ['id' => $term->id])->stretched())->sort(),
            TD::make('id', 'ID')->render(fn(LegalDocument $term) => Link::make((string)$term->id())),
            TD::make('active', 'Активно')->active()->sort(),
            TD::make('language', 'Язык')->render(fn(LegalDocument $term) => $term->getLanguage()->getLabel())->sort(),
            TD::make('published_at', 'Дата публикации')->render(fn(LegalDocument $term) => $term->published_at?->format('d.m.Y'))->sort(),
            TD::make('created_at', 'Создано')->render(fn(LegalDocument $term) => $term->created_at->format('d.m.Y'))->sort(),
            TD::make('updated_at', 'Обновлено')->render(fn(LegalDocument $term) => $term->updated_at?->format('d.m.Y h:i'))->sort(),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}
