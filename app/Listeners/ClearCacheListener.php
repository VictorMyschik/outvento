<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ClearCacheEvent;

final readonly class ClearCacheListener
{
    public function handle(ClearCacheEvent $event): void
    {
        $class = $event->class;
        $method = $event->method;

        if (method_exists($class, $method)) {
            $class::$method();
        } else {
            throw new \BadMethodCallException("Method {$method} does not exist in class {$class}");
        }
    }
}
