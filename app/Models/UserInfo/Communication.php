<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Resolvers\CommunicationChannelSupportResolver;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\VerificationStatus;
use App\Services\User\Enum\Visibility;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Communication extends ORM
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;
    use DescriptionNullableFieldTrait;

    use UserFieldTrait;

    protected $table = 'communications';
    protected $fillable = [
        'user_id',
        'type',// тип: телефон, email, факс...
        'address',
        'description',
    ];

    public $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected array $allowedSorts = [
        'user_id',
        'full_name',
        'address',
        'type',
        'email',
        'name',
        'description',
        'verified_at',
        'verification_status',
        'verification_token',
        'created_at',
        'updated_at',
    ];

    public function getType(): CommunicationType
    {
        return CommunicationType::from($this->type);
    }

    public function getVisibility(): Visibility
    {
        return Visibility::from($this->visibility);
    }

    public function getVerificationStatus(): VerificationStatus
    {
        return VerificationStatus::from($this->verification_status);
    }

    public function getChannel(): ?NotificationChannel
    {
        return CommunicationChannelSupportResolver::fromCommunicationType($this->getType());
    }

    public function getTelegramLink(): ?string
    {
        if ($this->getType() == CommunicationType::Telegram) {
            if (str_starts_with($this->address, '@')) {
                return 'https://t.me/' . substr($this->address, 1);
            }

            return 'https://t.me/' . $this->address;
        }

        return null;
    }

    public function getTelegramDeepLink(): ?string
    {
        if ($this->getType() !== CommunicationType::Telegram) {
            return null;
        }

        $botUsername = config('services.telegram-bot-api.bot_username');

        $payload = 'connect_' . $this->address_ext;

        return sprintf('https://t.me/%s?start=%s', $botUsername, $payload);
    }
}
