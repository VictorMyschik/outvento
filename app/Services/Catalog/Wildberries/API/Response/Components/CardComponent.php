<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response\Components;

final readonly class CardComponent
{
    public function __construct(
        public int     $nmID,
        public int     $imtID,
        public string  $nmUUID,
        public int     $subjectID,
        public string  $subjectName,
        public string  $vendorCode,
        public string  $brand,
        public string  $title,
        public array   $photos,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['nmID'],
            $data['imtID'],
            $data['nmUUID'],
            $data['subjectID'],
            $data['subjectName'],
            $data['vendorCode'],
            $data['brand'],
            $data['title'],
            $data['photos'],
            $data['description'] ?? null,
        );
    }
}
