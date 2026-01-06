<?php

declare(strict_types=1);

namespace App\Services\Catalog\Onliner;

use App\Models\Catalog\CatalogImage;
use App\Services\Catalog\Enum\CatalogImageTypeEnum;
use Illuminate\Http\UploadedFile;

interface ImageUploaderInterface
{
    public function uploadImage(UploadedFile $image, int $goodId, CatalogImageTypeEnum $type): CatalogImage;
}
