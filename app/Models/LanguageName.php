<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class LanguageName extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'language_names';

    public $timestamps = false;
    protected $fillable = [
        'language_id',
        'locale',
        'name',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}