<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum CommunicationTypeCode: string
{
    case Phone = 'phone';
    case Mail = 'mail';
    case Address = 'address';
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case Viber = 'viber';
    case Link = 'link';
    case Geocoordinates = 'geocoordinates';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('enums.communicate_type.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}