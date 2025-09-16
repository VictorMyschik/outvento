<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\Lego\Fields\JsonFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CatalogGroup extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use JsonFieldTrait;

    protected $table = 'catalog_groups';

    protected array $allowedSorts = [
        'id',
        'name',
        'json_link',
    ];

    protected $casts = [
        'id'        => 'int',
        'name'      => 'string',
        'json_link' => 'string',
    ];

    public function getJsonLink(): ?string
    {
        return $this->json_link;
    }

    public function getOnlinerArticleName(): ?string
    {
        $link = (string)$this->getJsonLink();

        if (strpos($link, 'page=1')) {
            $link = str_replace('?page=1', '', $link);
        }

        $tmpArr = explode('/', $link);

        return array_pop($tmpArr);
    }
}
