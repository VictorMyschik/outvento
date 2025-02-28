<?php

namespace App\Models\Lego\Fields;

use App\Services\System\Enum\Language;

trait NameByLanguageFieldTrait
{
    public function getName(Language $language): string
    {
        return $this->getAttribute('name_' . $language->getCode());
    }
}
