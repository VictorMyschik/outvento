<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Invitation extends Model
{
    use Notifiable, HasUuids;

    const null UPDATED_AT = null;

    protected $table = 'invitations';

    protected $fillable = [
        'id',
        'email',
        'travel_id',
    ];
}
