<?php

declare(strict_types=1);

namespace App\Helpers\System;

use Illuminate\Support\Facades\File;

abstract class MrBaseHelper
{
    public static function getShortLink(string $url): string
    {
        return @file_get_contents("https://clck.ru/--?url=" . $url) ?? $url;
    }

    public static function createDir(string $dir)
    {
        File::makeDirectory($dir, 0777, true, true);
    }

    private static array $date_words = array(
        ['год', 'года', 'лет'],
        ['месяц', 'месяца', 'месяцев'],
        ['неделя', 'недели', 'недель'],
        ['день', 'дня', 'дней'],
        ['сутки', 'суток', 'суток'],
        ['час', 'часа', 'часов'],
        ['минута', 'минуты', 'минут'],
        ['секунда', 'секунды', 'секунд'],
        ['микросекунда', 'микросекунды', 'микросекунд'],
        ['миллисекунда', 'миллисекунды', 'миллисекунд'],
    );

    public static function getBoolValueDisplay(bool $value): string
    {
        if ($value) {
            return "<i class='fa fa-check text-success'></i>";
        } else {
            return "<i class='fa fa-ban text-danger'></i>";
        }
    }

    public static function generateUniqueID(string $soul): string
    {
        return substr($soul, 0, 8) . rand(100, 999) . uniqid();
    }

    /**
     * The maximum file upload size by getting PHP settings
     * @return float file size limit in BYTES based
     */
    public static function getMaxUploadSize(): float
    {
        $memoryLimit = self::strToBytes(ini_get('memory_limit'));
        $uploadMaxFilesize = self::strToBytes(ini_get('upload_max_filesize'));
        $postMaxSize = self::strToBytes(ini_get('post_max_size'));

        if (empty($postMaxSize) && empty($uploadMaxFilesize) && empty($memoryLimit)) {
            return false;
        }

        return (float)min($postMaxSize, $uploadMaxFilesize, $memoryLimit);
    }

    public static function strToBytes(string $value): ?int
    {
        $unitByte = preg_replace('/[^a-zA-Z]/', '', $value);
        $unitByte = strtolower($unitByte);

        // only number (allow decimal point)
        $intValue = preg_replace('/[^0-9]/', '', $value);

        switch ($unitByte) {
            case 'p':    // petabyte
            case 'pb':
                $intValue *= 1024;
            // no break
            case 't':    // terabyte
            case 'tb':
                $intValue *= 1024;
            // no break
            case 'g':    // gigabyte
            case 'gb':
                $intValue *= 1024;
            // no break
            case 'm':    // megabyte
            case 'mb':
                $intValue *= 1024;
            // no break
            case 'k':    // kilobyte
            case 'kb':
                $intValue *= 1024;
            // no break
            case 'b':    // byte
            case '':     // byte
                return (int)$intValue;
            default:
                return null;
        }
    }
}
