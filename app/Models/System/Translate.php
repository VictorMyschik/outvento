<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\ORM\ORM;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        'language_id',
        'translate',
    );

    protected array $allowedSorts = [
        'code',
        'language_id',
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
        return Language::loadByOrDie($this->language_id);
    }

    /**
     * Язык перевода
     */
    public function setLanguageID(int $value): void
    {
        $this->language_id = $value;
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

    public static function getFullList(string $code): array
    {
        return Cache::rememberForever('translate_list_' . $code, function () use ($code) {
            $list = DB::table(Translate::getTableName())
                ->join('languages', 'translates.language_id', '=', 'languages.id')
                ->select('translates.*')
                ->where('languages.code', strtoupper($code))
                ->get(['translates.code', 'translates.translate'])->toArray();

            $out = [];
            foreach ($list as $value) {
                $out[$value->code] = $value->translate;
            }

            return $out;
        });
    }
}
