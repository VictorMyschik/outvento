<?php

namespace App\Models;

use App\Models\ORM\ORM;
use App\Services\System\Enum\Language;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Faq extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'faq';
    protected $fillable = array(
        'id',
        'language',
        'title',
        'text',
        'active'
    );

    protected array $allowedSorts = [
        'id',
        'language_id',
        'title',
        'text',
        'active'
    ];

    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): void
    {
        $this->title = $value;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $value): void
    {
        $this->text = $value;
    }
}
