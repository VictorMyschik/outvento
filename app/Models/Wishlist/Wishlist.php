<?php

declare(strict_types=1);

namespace App\Models\Wishlist;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Wishlist extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'wishlists';

    protected $fillable = [
        'category',
        'subcategory',
        'title',
        'url',
        'price',
        'currency',
        'user_id',
        'created_at',
        'updated_at',
        'archived_at',
    ];

    protected array $allowedSorts = [
        'id',
        'category',
        'subcategory',
        'title',
        'price',
        'currency',
        'user_id',
        'created_at',
        'updated_at',
        'archived_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'archived_at' => 'datetime',
    ];
}