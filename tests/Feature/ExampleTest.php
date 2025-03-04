<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Services\Travel\Enum\UITStatus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $list = Travel::all();
        foreach ($list as $travel) {
            for ($i = 0; $i < $travel->getMaxMembers(); $i++) {
                if (!rand(0, 4)) {
                    break;
                }
                $result = DB::table(UIT::getTableName())->insertOrIgnore([
                    'travel_id' => $travel->id(),
                    'user_id'   => DB::table('users')->inRandomOrder()->first()->id,
                    'status'    => UITStatus::CONFIRMED,
                ]);
                if ($result) {
                    DB::table(Travel::getTableName())->where('id', $travel->id())->increment('members_exists');
                }
            }
        }
    }
}
