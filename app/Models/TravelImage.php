<?php

namespace App\Models;

use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\KindFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TravelImage extends ORM
{
    use NameFieldTrait;
    use CreatedFieldTrait;
    use KindFieldTrait;
    use DescriptionNullableFieldTrait;
    use UserFieldTrait;

    protected $table = 'travel_images';

    public $timestamps = false;

    protected $fillable = [
        'travel_id',
        'kind',
        'name',
    ];

    const KIND_LOGO = 0;
    const KIND_LIST = 1;

    public static function getKindList(): array
    {
        return [
            self::KIND_LOGO => 'Главное',
            self::KIND_LIST => 'Список',
        ];
    }

    #region ORM
    public function canView(?User $user): bool
    {
        $travel = $this->getTravel();

        if (!$travel->canView($user)) {
            return false;
        }

        return true;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (!$this->canView($user)) {
            return false;
        }

        // is author of image
        if ($user->id() === $this->getUser()->id() || $user->id() === $this->getTravel()->getUser()->id()) {
            return true;
        }

        return false;
    }

    public function afterSave(): void
    {
        $this->flushAffectedCaches();
    }

    public function beforeDelete(): void
    {
        $images = DB::table(TravelImage::getTableName())->where('name', $this->getName())->count();

        if ($images === 1) {
            $this->deleteImageFromStorage();
        }

        $this->flushAffectedCaches();
    }

    public function flushAffectedCaches(): void
    {
        $this->getTravel()->flush();
    }

    #endregion

    public function getTravel(): Travel
    {
        return Travel::loadByOrDie($this->travel_id);
    }

    public function setTravelID(int $value): void
    {
        $this->travel_id = $value;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $value): void
    {
        $this->sort = $value;
    }

    public function getOriginalName(): string
    {
        return $this->original_name;
    }

    public function setOriginalName(string $value): void
    {
        $this->original_name = $value;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $value): void
    {
        $this->size = $value;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $value): void
    {
        $this->hash = $value;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $value): void
    {
        $this->group = $value;
    }

    public function getLocalPath(): string
    {
        return $this->getTravel()->getDirNameForImages() . DIRECTORY_SEPARATOR . $this->name;
    }

    private function deleteImageFromStorage(): void
    {
        $imagePath = $this->getTravel()->getDirNameForImages() . '/' . $this->getName();
        $imagePath = str_replace('storage/', '', $imagePath);
        Storage::delete($imagePath);
    }
}
