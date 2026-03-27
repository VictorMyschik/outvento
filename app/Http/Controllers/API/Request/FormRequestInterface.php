<?php

namespace App\Http\Controllers\API\Request;

use App\Services\Forms\FormInterface;
use App\Services\System\Enum\Language;

interface FormRequestInterface
{
    public function getDto(Language $language): FormInterface;
}
