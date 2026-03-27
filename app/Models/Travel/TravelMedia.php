<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;

class TravelMedia extends ORM
{
    protected $table = 'travel_media';

    public function getUrl(): ?string
    {
        return $this->avatar ? route('admin.travel.logo', ['travel' => $this->travel_id]) : null;
    }

    public function getAvatarExt()
    {
        return $this->getAvatar() ?: '/images/travel_logo_circle.webp';
    }
}