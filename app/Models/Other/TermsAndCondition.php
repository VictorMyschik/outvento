<?php

declare(strict_types=1);

namespace App\Models\Other;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TermsAndCondition extends ORM
{
    use AsSource;
    use Filterable;
    use TitleFieldTrait;
    use ActiveFieldTrait;
    use LanguageFieldTrait;

    protected $table = 'terms_and_conditions';
    protected $fillable = [
        'active',
        'language',
        'title',
        'text',
        'published_at',
    ];

    protected array $allowedSorts = [
        'id',
        'active',
        'group_id',
        'title',
        'published_at',
        'language',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'published_at' => 'datetime',
    ];
}