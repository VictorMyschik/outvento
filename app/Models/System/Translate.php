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
        'ru',
        'en',
        'pl',
    );

    protected array $allowedSorts = [
        'code',
        'ru',
        'en',
        'pl',
    ];

    public static function getFullList(Language $language): array
    {
        return Cache::rememberForever('translate_list_' . $language->getCode(), function () use ($language) {
            $list = Translate::select('code', $language->getCode())->get()->all();

            $field = $language->getCode();
            $out = [];
            foreach ($list as $value) {
                $out[$value->code] = $value->$field;
            }

            return $out;
        });
    }
}
