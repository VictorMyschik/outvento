<?php

declare(strict_types=1);

namespace Factories;

use App\Models\User;
use Tests\TestCase;

final class UserFactoryTest extends TestCase
{
    public function testUserFactoryCreatesValidUser(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
    }
}