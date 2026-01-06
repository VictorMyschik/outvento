<?php

declare(strict_types=1);

namespace App\Models\MessageLog;

use App\Models\ORM\ORM;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Notifications\Enum\NotificationType;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class EmailLog extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'email_logs';

    protected array $allowedSorts = ['id', 'type', 'email', 'subject', 'status', 'created_at'];

    public const null UPDATED_AT = null;

    protected $casts = [
        'sl' => 'json'
    ];

    public function getType(): NotificationType
    {
        return NotificationType::from((string)$this->type);
    }

    public function setType(NotificationType $value): void
    {
        $this->type = $value->value;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): void
    {
        $this->email = $value;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $value): void
    {
        $this->subject = $value;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $value): void
    {
        $this->status = $value;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $value): void
    {
        $this->error = $value;
    }

    public function setEmailBody(string $value): void
    {
        $this->sl = $value;
    }

    public function getBody(): ?string
    {
        return $this->sl;
    }
}
