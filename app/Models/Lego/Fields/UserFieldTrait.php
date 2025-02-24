<?php

namespace App\Models\Lego\Fields;

use App\Models\User;

trait UserFieldTrait
{
    public function getUser(): User
    {
        return User::find($this->user_id);
    }

    public function setUserID(int $value): void
    {
        $this->user_id = $value;
    }
}
