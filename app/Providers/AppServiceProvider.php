<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('album-comments-write', function (Request $request): array {
            $key = (string)($request->user()?->id ?? $request->ip());

            return [
                Limit::perMinute(20)->by($key),
                Limit::perHour(300)->by($key),
            ];
        });

        Blade::directive('Language', function (string $expression) {
            return "<?php echo App\Services\System\Enum\Language::fromCode({$expression})->getLabel(); ?>";
        });
    }
}
