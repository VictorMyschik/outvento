<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Manufacturer extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;

    protected $table = 'manufacturers';

    protected array $allowedSorts = [
        'id',
        'name',
        'address',
    ];

    protected $fillable = [
        'name',
        'address',
    ];

    public const null UPDATED_AT = null;

    protected $casts = [
        'id'         => 'int',
        'name'       => 'string',
        'address'    => 'string',
        'created_at' => 'datetime',
    ];

    public function getAddress(): ?string
    {
        return $this->address;
    }
}
