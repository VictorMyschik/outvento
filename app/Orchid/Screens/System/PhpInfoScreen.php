<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PhpInfoScreen extends Screen
{
    public string $name = 'PHP Info';

    public function query(): iterable
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        return [
            'phpinfo' => $phpinfo,
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('admin.php'),
        ];
    }
}
