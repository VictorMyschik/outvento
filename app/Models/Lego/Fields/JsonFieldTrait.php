<?php

namespace App\Models\Lego\Fields;

/**
 * Json данные
 */
trait JsonFieldTrait
{
    public function getSL()//: ?string
    {
        return $this->sl;
    }

    public function setSL(?string $value): void
    {
        $this->sl = $value;
    }

    public function getJsonField(string $field): mixed
    {
        if (is_null($this->getSL())) {
            return null;
        }

        $data = json_decode((string)$this->getSL(), true);

        return $data[$field] ?? null;
    }

    public function setJsonField(string $field, mixed $value): void
    {
        if (is_null($this->getSL())) {
            $data = array();
        } else {
            $data = json_decode($this->getSL(), true);
        }

        if (!$value) {
            unset($data[$field]);
        } else {
            $data[$field] = $value;
        }

        $value = json_encode($data);
        $this->setSL($value ?: null);
    }
}