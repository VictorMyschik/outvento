<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\DTO;

final readonly class WBGoodDto
{
    public function __construct(
        public int     $nm_id,
        public int     $imt_id,
        public int     $subject_id,
        public string  $vendor_code,
        public int     $brand_id,
        public string  $title,
        public ?string $description,
        public string  $sl,
    ) {}
}
