<?php

declare(strict_types=1);

namespace App\Forms\FormBase\Helpers;

final class Group
{
    public function __construct(public array $fields) {}

    public static function make(array $fields): self
    {
        return new self($fields);
    }

    public function getType(): string
    {
        return 'group';
    }
}
