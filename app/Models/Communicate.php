<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\KindFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Screen\AsSource;

class Communicate extends ORM
{
    use AsSource;
    use DescriptionNullableFieldTrait;
    use KindFieldTrait;

    use UserFieldTrait;

    protected $table = 'communicates';
    protected $fillable = [
        'user_id',
        'kind',// тип: телефон, email, факс...
        'address',
        'description'
    ];

    const CODE_TE = 1;
    const CODE_EM = 2;
    const CODE_AO = 3;

    private static array $addressKinds = array(
        self::CODE_TE => 'Телефон',
        self::CODE_EM => 'Электронная почта',
        self::CODE_AO => 'URL',
    );

    private static array $kindCodes = array(
        self::CODE_TE => 'TE',
        self::CODE_EM => 'EM',
        self::CODE_AO => 'AO',
    );

    private static array $kind_icons = array(
        self::CODE_TE => 'fa fa-phone',
        self::CODE_EM => 'fa fa-envelope',
        self::CODE_AO => 'fa fa-url',
    );


    public function getKindIcon(): string
    {
        return self::$kind_icons[$this->getKind()];
    }

    public static function getKindCodes(): array
    {
        return self::$kindCodes;
    }

    public function getKindCode(): string
    {
        return self::$kindCodes[$this->getKind()];
    }

    public static function getKindList(): array
    {
        return self::$addressKinds;
    }

    // Адрес
    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $value): void
    {
        $this->address = $value;
    }

    ///////////////////////////////////////////////////////////////////////
    public function getFullAddress(): string
    {
        $r = '';
        $icon = $this->getKindIcon();
        $r .= "<i class='fa {$icon}'></i> ";
        $r .= $this->getAddress();

        return $r;
    }
}
