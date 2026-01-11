<?php

namespace App\Helpers\System;

use App\Http\Controllers\Controller;

class MrLink extends Controller
{
    public static function open(string $route_name, array $arguments, ?string $text, string $class = null, $title = '', array $attr = array()): string
    {
        $out = array();

        $out['url'] = route($route_name, $arguments);
        $out['arguments'] = $arguments;
        $out['text'] = $text ?? '';
        $out['class'] = $class ?? '';
        $out['title'] = $title;
        $out['attributes'] = $attr;

        return View('layouts.Elements.link')->with($out)->toHtml();
    }

    public static function AddDangerBtn(string $route_name, array $arguments, ?string $text, string $class = '', $title = '', array $attr = array()): string
    {
        $class = 'btn mr-btn-danger btn-sm fa ' . $class;
        $attr = $attr + ['onclick' => 'return confirm("Уверены?");'];

        return self::open($route_name, $arguments, $text, $class, $title, $attr);
    }

    public static function AddSuccessBtn(string $route_name, array $arguments, ?string $text, string $class = '', $title = '', array $attr = array()): string
    {
        $class .= ' btn mr-btn-success btn-sm fa ' . $class;

        return self::open($route_name, $arguments, $text, $class, $title, $attr);
    }

    public static function AddWarningBtn(string $route_name, array $arguments, ?string $text, string $class = '', $title = '', array $attr = array()): string
    {
        $class .= 'btn mr-btn-danger btn-sm fa ' . $class;

        return self::open($route_name, $arguments, $text, $class, $title, $attr);
    }

    public static function addPrimaryBtn(string $route_name, array $arguments, ?string $text, string $class = '', $title = '', array $attr = array()): string
    {
        $class = 'btn mr-btn-primary btn-sm fa ' . $class;

        return self::open($route_name, $arguments, $text, $class, $title, $attr);
    }
}
