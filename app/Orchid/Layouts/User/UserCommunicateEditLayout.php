<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\UserInfo\CommunicationType;
use App\Services\User\Enum\CommunicationTypeCode;
use App\Services\User\Enum\VerificationStatus;
use App\Services\User\Enum\Visibility;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class UserCommunicateEditLayout extends Listener
{
    protected $targets = [
        'type_id',
    ];

    protected function layouts(): iterable
    {
        $out[] = Relation::make('user_id')
            ->fromModel(\App\Models\User::class, 'name', 'id')
            ->value(request()->get('user_id'))
            ->title('User');

        $out[] = Select::make('visibility')
            ->options(Visibility::getSelectList())
            ->value(request()->get('visibility'))
            ->title('Visibility');

        $out[] = Relation::make('type_id')
            ->fromModel(CommunicationType::class, 'title', 'id')
            ->value(request()->get('type_id'))
            ->title('Type');

        if (request()->get('type_id') || $this->query->get('type_id')) {
            $type = CommunicationType::loadByOrDie((int)request()->get('type_id') ?: $this->query->get('type_id'));

            if ($type->getCode() === CommunicationTypeCode::Mail) {
                $out[] = Select::make('verification_status')
                    ->value((bool)request()->get('verification_status'))
                    ->options(VerificationStatus::getSelectList())
                    ->title('Verification status');
            }
        }

        $out[] = Input::make('address')
            ->value(request()->get('address'))
            ->title('Address');

        $out[] = Input::make('description')
            ->value(request()->get('description'))
            ->title('Description');

        return [Layout::rows($out)];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository
            ->set('type_id', $request->input('type_id'));
    }
}
