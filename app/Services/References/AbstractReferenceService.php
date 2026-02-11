<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\References\Enum\ImageTypeEnum;
use App\Services\System\Enum\Language;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract readonly class AbstractReferenceService
{
    public function __construct(
        private ImageRepositoryInterface     $imageRepository,
        protected ReferenceRepositoryInterface $repository,
    ) {}

    public function getCountrySelectList(Language $language): array
    {
        return $this->repository->getCountrySelectList($language);
    }

    public function saveCountry(int $id, array $data): int
    {
        return $this->repository->saveCountry($id, $data);
    }

    public function getCurrencySelectList(): array
    {
        return $this->repository->getCurrencySelectList();
    }

    public function getTravelTypeSelectList(Language $language): array
    {
        return $this->repository->getTravelTypeSelectList($language);
    }



    public function saveCity(int $id, array $data): int
    {
        return $this->repository->saveCity($id, $data);
    }

    protected function saveImage(ImageTypeEnum $type, UploadedFile $file): string
    {
        return $this->imageRepository->saveImage($type, $file);
    }

    public function deleteImage(Model $model): void
    {
        $model->getImagePath() && $this->imageRepository->deleteImage($model->getImagePath());

        $model->image_path = null;
        $model->save();
    }
}
