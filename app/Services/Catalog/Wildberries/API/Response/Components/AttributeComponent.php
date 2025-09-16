<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response\Components;

final readonly class AttributeComponent
{
    public function __construct(
        public int    $charcID,
        public string $subjectName,
        public int    $subjectID,
        public string $name,
        public bool   $required,
        public string $unitName,
        public int    $maxCount,
        public bool   $popular,
        public int    $charcType,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            charcID: $data['charcID'],
            subjectName: $data['subjectName'],
            subjectID: $data['subjectID'],
            name: $data['name'],
            required: $data['required'],
            unitName: $data['unitName'],
            maxCount: $data['maxCount'],
            popular: $data['popular'],
            charcType: $data['charcType'],
        );
    }
}
