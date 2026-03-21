<?php

declare(strict_types=1);

namespace App\Models\Conversations;

use App\Models\ORM\ORM;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Conversation extends ORM
{
    use AsSource;
    use Filterable;
    use HasUlids;

    public $incrementing = false;

    protected $table = 'conversations';
}