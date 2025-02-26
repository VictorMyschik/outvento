<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\ORM\ORM;
use App\Services\System\Enum\Language;
use Illuminate\Support\Facades\Cache;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Translate extends ORM
{
    use AsSource;
    use Filterable;

    const null UPDATED_AT = null;

    protected $table = 'translates';

    protected $fillable = array(
        'code',
        'language',
        'translate',
    );

    protected array $allowedSorts = [
        'code',
        'language',
        'translate',
    ];

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $value): void
    {
        $this->code = $value;
    }

    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }

    /**
     * Переведено
     */
    public function getTranslate(): ?string
    {
        return $this->translate;
    }

    public function setTranslate(string $value): void
    {
        $this->translate = $value;
    }

    public static function getFullList(Language $language): array
    {
        return Cache::rememberForever('translate_list_' . $language->value, function () use ($language) {
            $list = Translate::where('language', $language->value)->get()->all();

            $out = [];
            foreach ($list as $value) {
                $out[$value->code] = $value->translate;
            }

            return $out;
        });
    }
}
