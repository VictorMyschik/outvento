<?php

declare(strict_types=1);

namespace App\Services\System\Enum;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

enum Language: int
{
    case EN = 1;
    case RU = 2;

    public static function fromString(string $language): self
    {
        return match (mb_strtolower($language)) {
            'ru' => self::RU,
            'en' => self::EN,
            default => throw new NotFoundHttpException('Unknown language: ' . $language),
        };
    }

    /**
     * @return string[]
     */
    public static function getSelectList(): array
    {
        return [
            self::RU->value => self::RU->getLabel(),
            self::EN->value => self::EN->getLabel(),
        ];
    }

    public function getCode(): string
    {
        return match ($this) {
            Language::RU => 'ru',
            Language::EN => 'en',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            Language::EN => 'English',
            Language::RU => 'Русский',
        };
    }

    public static function list(): array
    {
        return [
            self::EN,
            self::RU,
        ];
    }

    public function name(): string
    {
        return $this->getLabel();
    }
}
