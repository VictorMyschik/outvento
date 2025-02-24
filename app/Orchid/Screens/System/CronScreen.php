<?php

namespace App\Orchid\Screens\System;

use App\Models\System\Cron;
use App\Orchid\Layouts\System\Cron\CronEditLayout;
use App\Orchid\Layouts\System\Cron\CronListLayout;
use App\Services\System\CronService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CronScreen extends Screen
{
    private const int ALL_ACTIVE = -1;

    protected ?string $name = 'Cron';

    public function __construct(private readonly CronService $service) {}

    public function query(): iterable
    {
        return [
            'list' => Cron::filters([])->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('cron_modal')
                ->modalTitle('Create New Cron Job')
                ->method('saveCron')
                ->asyncParameters(['id' => 0]),

            Button::make('run all active')
                ->class('mr-btn-danger')
                ->icon('refresh')
                ->method('run', ['id' => self::ALL_ACTIVE])
                ->confirm('Run all Cron jobs'),
        ];
    }

    public function layout(): iterable
    {
        return [
            CronListLayout::class,
            Layout::modal('cron_modal', CronEditLayout::class)->async('asyncGetCron'),
        ];
    }

    public function asyncGetCron(int $id = 0): iterable
    {
        return [
            'cron' => Cron::loadBy($id) ?: new Cron(),
        ];
    }

    public function saveCron(Request $request): void
    {
        $cron = Cron::loadBy((int)$request->get('id')) ?: new Cron();
        $cron->setActive((bool)$request->get('cron')['active']);
        $cron->setCronKey($request->get('cron')['cron_key']);
        $cron->setPeriod((int)$request->get('cron')['period']);
        $cron->setDescription($request->get('cron')['description']);
        $cron->save();
    }

    public function remove(int $id): void
    {
        Cron::loadBy($id)->delete();
    }

    public function run(int $id): void
    {
        if ($id === self::ALL_ACTIVE) {
            $this->service->runAllActive();
            return;
        }
        $this->service->run(Cron::loadByOrDie($id));
    }
}
