<?php

declare(strict_types=1);

namespace App\Orchid\Rebuild;

use Orchid\Screen\Layouts\Tabs;

/**
 * Class CorrectTabs.
 */
class MrTabs extends Tabs
{
    public function __construct(array $layouts = [])
    {
        parent::__construct($layouts);
    }

    public static function make(array $layouts = [])
    {
        return new self($layouts);
    }

    public function setTitle(string $title): static
    {
        $this->variables['title'] = $title;
        return $this;
    }
}
