<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Events\FormRequestEvent;
use App\Models\Forms\Form;
use App\Services\Forms\Enum\FormTypeEnum;

final readonly class FormService
{
    public function __construct(private FormRepositoryInterface $repository) {}

    public function addForm(FormInterface $dto): void
    {
        $dto->setID($this->repository->addForm($dto));

        if ($this->isEmailEnabled($dto->getType())) {
            event(new FormRequestEvent($dto));
        }
    }

    public function getFormById(int $formId): ?Form
    {
        return $this->repository->getFormById($formId);
    }

    public function deleteForm(int $id): void
    {
        $this->repository->deleteForm($id);
    }

    public function saveFormComment(int $formId, array $data): void
    {
        $this->repository->saveFormComment($formId, $data);
    }

    public function deleteAllRequestsByType(FormTypeEnum $type): void
    {
        $this->repository->deleteAllRequestsByType($type);
    }

    public function deleteAllRequests(): void
    {
        $this->repository->deleteAllRequests();
    }

    private function isEmailEnabled(FormTypeEnum $formType): bool
    {
        return in_array($formType, [
            FormTypeEnum::Feedback,
        ]);
    }
}
