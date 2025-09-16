<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries;

use App\Models\Catalog\Wildberries\WBCatalogImage;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use Illuminate\Http\UploadedFile;

interface ImageUploaderInterface
{
    public function uploadImage(UploadedFile $image, int $goodId, CatalogImageTypeEnum $type): WBCatalogImage;
}
