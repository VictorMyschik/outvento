<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Models\Forms\Form;
use App\Services\Forms\Enum\FormType;

interface FormRepositoryInterface
{
    public function addForm(FormInterface $dto): int;

    public function deleteForm(int $id): void;

    public function saveFormComment(int $formId, array $data): void;

    public function getFormById(int $formId): ?Form;

    public function deleteAllRequestsByType(FormType $type): void;

    public function deleteAllRequests(): void;

    public function runAllAsRead(): void;
}
