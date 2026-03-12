<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\Service\TravelInviteNotification;
use App\Services\Travel\DTO\TravelInviteDto;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::find(1);

        $user->notify(new TravelInviteNotification(
            new TravelInviteDto(
                userId: $user->id,
                activities: [],
                countryLabels: [],
                confirmationUrl: '1234',
            )
        ));
    }

}
