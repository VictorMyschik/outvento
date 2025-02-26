<?php

declare(strict_types=1);

namespace App\Repositories\Forms;

use App\Models\Forms\Form;
use App\Repositories\DatabaseRepository;
use App\Services\Forms\Enum\FormTypeEnum;
use App\Services\Forms\FormInterface;
use App\Services\Forms\FormRepositoryInterface;

final class FormDBRepository extends DatabaseRepository implements FormRepositoryInterface
{
    public function addForm(FormInterface $dto): int
    {
        return $this->db->table(Form::getTableName())->insertGetId([
            'language' => $dto->getLanguage()->value,
            'type'     => $dto->getType()->value,
            'sl'       => $dto->getJson(),
        ]);
    }

    public function deleteForm(int $id): void
    {
        $this->db->table(Form::getTableName())->where('id', $id)->delete();
    }

    public function saveFormComment(int $formId, array $data): void
    {
        $data['updated_at'] = now();
        $this->db->table(Form::getTableName())->where('id', $formId)->update($data);
    }

    public function getFormById(int $formId): ?Form
    {
        return Form::loadBy($formId);
    }

    public function deleteAllRequestsByType(FormTypeEnum $type): void
    {
        $this->db->table(Form::getTableName())->where('type', $type->value)->delete();
    }

    public function deleteAllRequests(): void
    {
        $this->db->table(Form::getTableName())->truncate();
    }
}
