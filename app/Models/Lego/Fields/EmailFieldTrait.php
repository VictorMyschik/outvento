<?php

namespace App\Models\Lego\Fields;

trait EmailFieldTrait
{
  // Эл. почта
  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $value): void
  {
    $this->email = $value;
  }
}