<?php

declare(strict_types=1);

namespace App\Services\Email\Enum;

enum EmailTypeEnum: int
{
    case INVITE = 1;
    case FEEDBACK = 2;
    case NEWS = 3;

    public function getLabel(): string
    {
        return match ($this) {
            EmailTypeEnum::INVITE => 'Заявка в Travel',
            EmailTypeEnum::FEEDBACK => 'Обратная связь',
            EmailTypeEnum::NEWS => 'Новости',
        };
    }

    public static function getSelectList(): array
    {
        return [
            EmailTypeEnum::INVITE->value   => EmailTypeEnum::INVITE->getLabel(),
            EmailTypeEnum::FEEDBACK->value => EmailTypeEnum::FEEDBACK->getLabel(),
            EmailTypeEnum::NEWS->value     => EmailTypeEnum::NEWS->getLabel(),
        ];
    }
}
