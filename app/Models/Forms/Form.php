<?php

declare(strict_types=1);

namespace App\Models\Forms;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use App\Models\User;
use App\Services\Forms\Enum\FormType;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Form extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use LanguageFieldTrait;
    use ActiveFieldTrait;

    protected $table = 'forms';

    protected array $allowedSorts = [
        'id',
        'language',
        'active',
        'type',
        'user_id',
        'contact',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'sl'         => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function getType(): FormType
    {
        return FormType::from($this->type);
    }

    public function getEmail(): ?string
    {
        return $this->getSL()['email'] ?? null;
    }

    public function getUser(): ?User
    {
        return User::find($this->user);
    }

    public function getName(): ?string
    {
        return $this->getSL()['name'] ?? null;
    }

    public function getMessage(): ?string
    {
        return $this->getSL()['message'] ?? null;
    }

    public function getSL(): array
    {
        return $this->sl;
    }
}
