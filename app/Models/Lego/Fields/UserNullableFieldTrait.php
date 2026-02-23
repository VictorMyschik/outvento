<?php

declare(strict_types=1);

namespace App\Models\Lego\Fields;

use App\Models\User;

trait UserNullableFieldTrait
{
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    public function setUserID(?int $value): void
    {
        $this->user_id = $value;
    }
}
