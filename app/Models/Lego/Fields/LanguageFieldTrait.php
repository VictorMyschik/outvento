<?php

namespace App\Models\Lego\Fields;

use App\Services\System\Enum\Language;

trait LanguageFieldTrait
{
    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }
}
