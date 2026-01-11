<?php

declare(strict_types=1);

namespace App\Jobs\Catalog;

use App\Jobs\Enum\QueueJob;
use App\Models\Catalog\CatalogGroup;
use App\Services\Catalog\Onliner\ImportOnlinerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SearchGoodsByCatalogGroupJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $groupId)
    {
        $this->queue = QueueJob::Catalog->value;
    }

    public function handle(ImportOnlinerService $service): void
    {
        $service->searchNewGoodsByCatalogGroup(CatalogGroup::loadByOrDie($this->groupId));
    }
}
