<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UserLanguage extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'user_languages';

    public $timestamps = false;
    protected $fillable = [
        'id',
        'user_id',
        'language_id',
        'level',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getUser(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function getLanguage(): Language
    {
        return Language::loadByOrDie((int)$this->language_id);
    }
}