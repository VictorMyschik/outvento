<?php

declare(strict_types=1);

namespace App\Services\Catalog\Onliner\DTO;

use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use App\Services\Catalog\Enum\MediaTypeEnum;
use JsonSerializable;

final readonly class ImageDTO implements JsonSerializable
{
    public function __construct(
        public int                  $good_id,
        public ?string              $original_url,
        public ?string               $path,
        public string               $hash,
        public CatalogImageTypeEnum $type,
        public MediaTypeEnum        $media_type,
    ) {}

    public function toArray(): array
    {
        return [
            'good_id'      => $this->good_id,
            'original_url' => $this->original_url,
            'path'         => $this->path,
            'hash'         => $this->hash,
            'type'         => $this->type->value,
            'media_type'   => $this->media_type->value,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'good_id'      => $this->good_id,
            'original_url' => $this->original_url,
            'path'         => $this->path,
            'hash'         => $this->hash,
            'type'         => $this->type->value,
            'media_type'   => $this->media_type->value,
        ];
    }
}
