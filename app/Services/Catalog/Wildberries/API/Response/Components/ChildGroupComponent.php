<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response\Components;

final readonly class ChildGroupComponent
{
    public function __construct(
        public int    $subjectID,
        public int    $parentID,
        public string $subjectName,
        public string $parentName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subjectID: $data['subjectID'],
            parentID: $data['parentID'],
            subjectName: $data['subjectName'],
            parentName: $data['parentName'],
        );
    }
}
