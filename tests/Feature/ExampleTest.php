<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Notification\NotificationEventType;
use App\Models\Notification\UserNotificationSetting;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $r = DB::table(NotificationEventType::getTableName())->join('model_roles', 'model_roles.model_id', '=', NotificationEventType::getTableName() . '.id')
            ->where('model_roles.table_name', NotificationEventType::class)
            ->whereIn('model_roles.role_id', [1, 2])
            ->groupBy(NotificationEventType::getTableName() . '.id')
            ->get(NotificationEventType::getTableName() . '.title')->toArray();
    }

    private function generateString(string $namespace, array|string $fromFile): array
    {
        $out = [];

        if (is_array($fromFile)) {
            foreach ($fromFile as $key => $value) {

                $currentNamespace = $namespace . '.' . $key;

                if (is_string($value)) {
                    $out[$currentNamespace] = $value;
                    continue;
                }

                if (is_array($value)) {
                    $out = array_merge($out, $this->generateString($currentNamespace, $value));
                } else {
                    return $value;
                }
            }
        }

        if (is_string($fromFile)) {
            $out[$namespace] = $fromFile;
        }

        return $out;
    }
}
