<?php

namespace App\Http\Controllers\Forms\Exception;

use App\Exceptions\ExceptionAPIBase;

class FormException extends ExceptionAPIBase
{
    protected $message = 'Input missing';
}
