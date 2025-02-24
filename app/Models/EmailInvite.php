<?php

namespace App\Models;

use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\TravelFieldTrait;
use App\Models\Lego\Fields\UpdatedNullableFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class EmailInvite extends ORM
{
    use AsSource;
    use Filterable;

    use UserFieldTrait;
    use TravelFieldTrait;
    use CreatedFieldTrait;
    use UpdatedNullableFieldTrait;

    protected $table = 'email_invite';

    protected $fillable = [
        'travel_id',
        'email',
        'token',
        'status',
    ];

    const STATUS_NEW = 0; // Новый
    const STATUS_SEND = 1; // Отправлен
    const STATUS_ERROR = 2; // Ошибка

    public static function getStatusList(): array
    {
        return [
            self::STATUS_NEW   => 'Новый',
            self::STATUS_SEND  => 'Отправлен',
            self::STATUS_ERROR => 'Ошибка',
        ];
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusName(): string
    {
        return self::getStatusList()[$this->status];
    }

    public function setStatus(int $value): void
    {
        $this->status = $value;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): void
    {
        $this->email = $value;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $value): void
    {
        $this->token = $value;
    }

    public function generateToken(): string
    {
        abort_if(empty($this->email), 500, 'Email ID is empty');
        return md5($this->travel_id . $this->email);
    }
}
