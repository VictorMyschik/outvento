<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;
use App\Services\Albums\Enum\Visibility;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Album extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'albums';

    protected $table = self::TABLE;

    public $fillable = [
        'user_id',
        'title',
        'description',
        'visibility',
        'avatar',
    ];

    protected array $allowedSorts = [
        'id',
        'title',
        'user_id',
        'visibility',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at',
        'updated_at',
    ];

    public function getVisibility(): Visibility
    {
        return Visibility::from($this->visibility);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar ? route('api.v1.album.avatar', ['album' => $this->id]) : null;
    }
}