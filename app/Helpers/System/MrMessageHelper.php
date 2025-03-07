<?php

declare(strict_types=1);

namespace App\Helpers\System;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class MrMessageHelper extends Controller
{
    const int KIND_ERROR = 1;
    const int KIND_WARNING = 2;
    const int KIND_SUCCESS = 3;

    protected static array $kind_list = array(
        self::KIND_ERROR   => 'danger',
        self::KIND_WARNING => 'warning',
        self::KIND_SUCCESS => 'success',
    );

    public static function setMessage(int $kind, string $message): void
    {
        $name = 'alert-' . self::$kind_list[$kind];

        Session::flash($name, $message);
    }

    public static function setError(string $message): void
    {
        self::setMessage(self::KIND_ERROR, $message);
    }

    /**
     * Display message
     */
    public static function getMessage(): ?string
    {
        $out = null;

        foreach (self::$kind_list as $kind) {
            if ($message = session('alert-' . $kind)) {
                $out .= "'<alert_modal message='$message'></alert_modal>'";
            }
        }

        return $out;
    }
}
