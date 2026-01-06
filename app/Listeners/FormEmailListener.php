<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FormRequestEvent;

final readonly class FormEmailListener
{
    public function handle(FormRequestEvent $event): void
    {
        // TODO: Оповестить админа
    }
}
