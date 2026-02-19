<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LanguageName;
use App\Models\UserLanguage;
use DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $r = DB::table(LanguageName::getTableName())
            ->join(UserLanguage::getTableName(), LanguageName::getTableName() . '.language_id', '=', UserLanguage::getTableName() . '.language_id')
            ->where(UserLanguage::getTableName() . '.user_id', 102)
            ->where(LanguageName::getTableName() . '.locale', 'ru')
            ->pluck(LanguageName::getTableName() . '.name', UserLanguage::getTableName() . '.language_id')
            ->all();

    }

}
