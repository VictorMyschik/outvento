<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Profile;

use App\Services\Notifications\Enum\NotificationChannel;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class UserNotificationSettingsEditLayout extends Rows
{
    public function fields(): array
    {
        $userSettings = $this->query->get('userSettings', []);
        $userCommunications = $this->query->get('userCommunications', []);
        $notificationEventTypes = $this->query->get('notificationEventTypes', []);

        $groups = [];
        foreach ($notificationEventTypes as $eventType) {
            $group = [];
            $group['Событие'] = Label::make($eventType->getTitle())->value($eventType->getTitle());


            foreach (NotificationChannel::cases() as $channel) {
                $value = null;
                $options = [];

                foreach ($userCommunications as $communication) {
                    if ($communication->code === $channel->value) {
                        $options[$communication->id] = $communication->address;

                        foreach ($userSettings as $setting) {
                            if ($setting->event_type_id === $eventType->id && $setting->communication_id === $communication->id) {
                                $value = $communication->id;
                                break;
                            }
                        }
                    }
                }

                $group[$channel->getLabel()] = Select::make($eventType->code . '|' . $channel->value)
                    ->options($options)
                    ->value($value)
                    ->empty('Не выбрано');
            }

            $groups[] = $group;
        }

        return [
            ViewField::make('')->view('admin.users.notification_settings')->value($groups),
        ];
    }
}