<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CatalogAttributeValue extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'catalog_attribute_values';

    protected $fillable = [
        'catalog_attribute_id',
        'text_value',
    ];

    public $timestamps = false;

    public function getTextValue(): ?string
    {
        return $this->text_value;
    }

    public function getCatalogAttributeId(): int
    {
        return $this->catalog_attribute_id;
    }

    public function setCatalogAttributeID(int $value): void
    {
        $this->catalog_attribute_id = $value;
    }
}
