<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Profile;

use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class UserNotificationSettingsEditLayout extends Rows
{
    public function fields(): array
    {
        $eventType = $this->query->get('notificationEventType');
        if (!$eventType) {
            return [];
        }

        $userSettings = $this->query->get('userSettings', []);
        $userCommunications = $this->query->get('userCommunications', []);

        $eventType = ServiceEvent::from($eventType);

        foreach (NotificationChannel::getCasesOutList() as $channel) {
            $group['Event'] = Label::make((string)$eventType->value)->value($eventType->getLabel());
            $group['Active'] = Switcher::make('active')->value($this->query->get('active', false))->sendTrueOrFalse();
            $value = null;
            $options = [];

            foreach ($userCommunications as $communication) {
                if ($communication->getChannel() === $channel) {
                    $options[$communication->id] = $communication->address;

                    foreach ($userSettings as $setting) {
                        if ($setting->event === $eventType->value && $setting->communication_id === $communication->id) {
                            $value = $communication->id;
                            break;
                        }
                    }
                }
            }

            $group[$channel->getLabel()] = Select::make('channel.' . $channel->value)
                ->options($options)
                ->value($value)
                ->empty('Не выбрано');
        }

        return [
            ViewField::make('')->view('admin.users.notification_settings')->value([$group]),
        ];
    }
}