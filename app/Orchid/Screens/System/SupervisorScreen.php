<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Orchid\Layouts\System\SupervisorListLayout;
use App\Services\System\Supervisor\SupervisorService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class SupervisorScreen extends Screen
{
    public string $name = 'Supervisor';

    public function __construct(private readonly SupervisorService $service) {}

    public function query(): iterable
    {
        return [
            'list' => collect($this->service->getList())->map(fn($item) => new Repository($item))
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Start all')
                ->class('mr-btn-success')
                ->icon('check-circle')
                ->confirm('Are you sure you want to stop all workers?')
                ->method('startAllWorkers'),
            Button::make('Stop all')
                ->class('mr-btn-danger')
                ->icon('ban')
                ->confirm('Are you sure you want to stop all workers?')
                ->method('stopAllWorkers'),
            Button::make('Restart all')
                ->class('mr-btn-danger')
                ->icon('refresh')
                ->confirm('Are you sure you want to stop all workers?')
                ->method('restartAllWorkers'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Group::make([
                    Select::make('group')->title('Group')->options($this->getGroupWorkers()),
                ]),
                ViewField::make('')->view('space'),
                Group::make([
                    Button::make('Start group')
                        ->class('mr-btn-success')
                        ->icon('check-circle')
                        ->confirm('Start group?')
                        ->method('startGroupWorkers'),
                    Button::make('Stop group')
                        ->class('mr-btn-danger')
                        ->icon('ban')
                        ->confirm('Stop group?')
                        ->method('stopGroupWorkers'),
                ])->autoWidth(),
            ]),

            SupervisorListLayout::class,
        ];
    }

    public function stopAllWorkers(): void
    {
        $this->service->stopAllWorkers();
    }

    public function startAllWorkers(): void
    {
        $this->service->startAllWorkers();
    }

    public function startGroupWorkers(Request $request): void
    {
        $this->service->startGroupWorkers($request->get('group', ''));
    }

    public function stopGroupWorkers(): void
    {
        $this->service->stopGroupWorkers(request()->get('group', ''));
    }

    public function restartAllWorkers(): void
    {
        $this->service->stopAllWorkers();
        sleep(1);
        $this->service->startAllWorkers();
    }

    private function getGroupWorkers(): array
    {
        foreach ($this->service->getList() as $item) {
            $groups[$item['group']] = $item['group'];
        }

        return $groups;
    }
}