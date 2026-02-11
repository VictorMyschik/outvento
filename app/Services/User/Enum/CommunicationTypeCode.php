<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum CommunicationTypeCode: string
{
    case Phone = 'phone';
    case Email = 'email';
    case Address = 'address';
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case Viber = 'viber';
    case Link = 'link';
    case Geocoordinates = 'geocoordinates';
    case Other = 'other';
}