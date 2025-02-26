<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Models\Forms\Form;
use App\Services\Forms\Enum\FormTypeEnum;

interface FormRepositoryInterface
{
    public function addForm(FormInterface $dto): int;

    public function deleteForm(int $id): void;

    public function saveFormComment(int $formId, array $data): void;

    public function getFormById(int $formId): ?Form;

    public function deleteAllRequestsByType(FormTypeEnum $type): void;

    public function deleteAllRequests(): void;
}
