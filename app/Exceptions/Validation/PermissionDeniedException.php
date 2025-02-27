<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ExceptionAPIBase;
use Illuminate\Http\Response;

class PermissionDeniedException extends ExceptionAPIBase
{
    protected $message = 'Permission denied';

    protected $code = Response::HTTP_FORBIDDEN;
}
