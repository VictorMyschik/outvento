<?php

declare(strict_types=1);

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Rows;

class FileDownloadLayout extends Rows
{
    public function fields(): array
    {
        return [
            Link::make('Скачать')
                ->icon('cloud-download')
                ->route('api.v1.download', [
                    'file' => $this->query->get('fileName'),
                    'name' => $this->query->get('downloadName'),
                    'disk' => $this->query->get('disk', 'public'),
                ])
                ->target('_blank')
        ];
    }
}
