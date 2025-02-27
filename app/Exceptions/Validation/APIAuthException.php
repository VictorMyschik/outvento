<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ExceptionAPIBase;

class APIAuthException extends ExceptionAPIBase
{
    protected $message = 'Unauthorized';
}
