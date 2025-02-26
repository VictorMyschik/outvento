<?php

namespace App\Http\Controllers\Api\Forms\Exception;

use App\Exceptions\ExceptionAPI;

class FormException extends ExceptionAPI
{
    protected $message = 'Input missing';
}
