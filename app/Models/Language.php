<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Language extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'languages';

    public $timestamps = false;
    protected $fillable = ['code'];

    protected array $allowedSorts = [
        'id',
        'code',
        'name', // Orchid list sorting
    ];

    public function names(): HasMany
    {
        return $this->hasMany(LanguageName::class);
    }
}