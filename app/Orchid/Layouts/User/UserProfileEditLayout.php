<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use App\Services\User\Enum\RelationshipStatus;
use App\Services\User\Enum\Visibility;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class UserProfileEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('name')->type('text')->max(255)->required()->title('Name (login)'),
                Input::make('email')->type('email')->required()->title('Email'),
            ]),

            Group::make([
                Select::make('visibility')
                    ->title('Profile Visibility')
                    ->options(Visibility::getSelectList()),

                Select::make('relationship_status')
                    ->title('Relationship status')
                    ->options(RelationshipStatus::getSelectList()),
            ]),

            Group::make([
                Input::make('first_name')->max(100)->title('First name'),
                Input::make('last_name')->max(100)->title('Last name'),
            ]),

            ViewField::make('')->view('admin.google-places'),

            Select::make('language')
                ->title('Language')
                ->options(Language::getSelectList()),

            Select::make('email_verified_at')
                ->title('Email verified')
                ->value((bool)$this->query->get('email_verified_at'))
                ->options([
                    1 => 'Verified',
                    0 => 'Not verified',
                ]),

            Group::make([
                Select::make('gender')
                    ->title('Gender')
                    ->options(Gender::getSelectList())
                    ->empty('[не выбрано]'),

                DateTimer::make('birthday')
                    ->title('Birthday')
                    ->serverFormat('d.m.Y')
                    ->format('d.m.Y'),
            ]),

            TextArea::make('about')
                ->title('About')
                ->rows(5)
        ];
    }
}
