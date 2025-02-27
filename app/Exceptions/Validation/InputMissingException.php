<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ExceptionAPIBase;

class InputMissingException extends ExceptionAPIBase
{
    protected $message = 'Input missing';
}
