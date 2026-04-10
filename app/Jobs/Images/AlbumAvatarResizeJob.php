<?php

declare(strict_types=1);

namespace App\Jobs\Images;

use App\Jobs\Enum\QueueJob;
use App\Services\Image\AlbumImageResizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AlbumAvatarResizeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $albumId)
    {
        $this->queue = QueueJob::Images->value;
    }

    public function handle(AlbumImageResizer $service): void
    {
        $service->resizeAvatar($this->albumId);
    }
}