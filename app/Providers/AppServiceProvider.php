<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('Language', function (string $expression) {
            return "<?php echo App\Services\System\Enum\Language::fromCode({$expression})->getLabel(); ?>";
        });
    }
}
