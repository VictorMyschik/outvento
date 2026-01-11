<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\Lego\Fields\ConstructorFileTrait;
use App\Models\ORM\ORM;

class ConstructorFile extends ORM
{
    use ConstructorFileTrait;

    protected $table = 'constructor_files';

    public $fillable = [
        'constructor_id',
        'type',
        'path',
        'sort',
        'file_name',
        'size',
        'extension',
    ];
}