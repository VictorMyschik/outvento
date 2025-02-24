<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Language extends ORM
{
    use AsSource;
    use Filterable;
    use ActiveFieldTrait;
    use NameFieldTrait;

    protected $table = 'languages';
    protected $fillable = [
        'active',
        'code', // english only code
        'name',
    ];

    protected array $allowedSorts = [
        'id',
        'name',
        'active',
        'code'
    ];

    public $timestamps = false;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $value): void
    {
        $this->code = mb_strtoupper($value);
    }

    /**
     * Текущий язык
     */
    public static function getCurrentLanguage(): self
    {
        $codeLocate = mb_strtoupper(app()->getLocale());
        return MrCacheHelper::getCachedData('language_by_locate', function () use ($codeLocate) {
            return self::where('code', $codeLocate)->first();
        });
    }

    public function getFullName(): string
    {
        return $this->getName() . ' (' . $this->getCode() . ')';
    }
}
