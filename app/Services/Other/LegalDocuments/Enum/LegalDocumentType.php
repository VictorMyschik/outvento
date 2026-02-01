<?php

declare(strict_types=1);

namespace App\Services\Other\LegalDocuments\Enum;

enum LegalDocumentType: string
{
    case Terms = 'terms';
    case Privacy = 'privacy';
    case Cookies = 'cookies';
    case Refund = 'refund';

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Terms => __('common.legal_documents.terms'),
            self::Privacy => __('common.legal_documents.privacy'),
            self::Cookies => __('common.legal_documents.cookies'),
            self::Refund => __('common.legal_documents.refund'),
        };
    }
}
