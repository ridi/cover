<?php
declare(strict_types=1);

namespace Ridibooks\Cover;

class CoverOptions
{
    const SUBDIRECTORY_NORMAL = '';
    const SUBDIRECTORY_TEST = 'test';

    const COLORSPACE_GRAYSCALE = 'grayscale';
    const COLORSPACE_TRUECOLOR = 'truecolor';

    private static $OPTIONS = [
        'size' => [ // size => width
            'small' => 60,
            'medium' => 80,
            'large' => 110,
            'xlarge' => 150,
            'xxlarge' => 320
        ],
        'dpi' => [ // dpi => scale
            'mdpi' => 1.0,
            'hdpi' => 1.5,
            'xhdpi' => 2.0,
            'xxhdpi' => 3.0
        ],
        'format' => [
            'jpg' => JpgBookCoverProvider::class,
            'png' => PngBookCoverProvider::class,
        ],
        'type' => [ // service_type => subdirectory
            'service' => self::SUBDIRECTORY_NORMAL,
            'test' => self::SUBDIRECTORY_TEST
        ],
        'display' => [ // type => colorspace
            'epd' => self::COLORSPACE_GRAYSCALE,
            'lcd' => self::COLORSPACE_TRUECOLOR,
        ]
    ];

    private static function get($option, $key, $default_key)
    {
        $key = array_key_exists($key, self::$OPTIONS[$option]) ? $key : $default_key;

        return self::$OPTIONS[$option][$key];
    }

    public static function getWidth($size)
    {
        return self::get('size', $size, 'medium');
    }

    public static function getScale($dpi): float
    {
        return self::get('dpi', $dpi, 'hdpi');
    }

    public static function getProviderClass($format): string
    {
        return self::get('format', $format, 'jpg');
    }

    public static function getSubdirectory($service_type): string
    {
        return self::get('type', $service_type, 'service');
    }

    public static function getColorspace($display)
    {
        return self::get('display', $display, 'lcd');
    }

    public static function getAvailableSizes(): array
    {
        return array_keys(self::$OPTIONS['size']);
    }

    public static function getAvailableDpis(): array
    {
        return array_keys(self::$OPTIONS['dpi']);
    }

    public static function getAvailableFormats(): array
    {
        return array_keys(self::$OPTIONS['format']);
    }

    public static function getAvailableTypes(): array
    {
        return array_keys(self::$OPTIONS['type']);
    }

    public static function getAvailableDisplays(): array
    {
        return array_keys(self::$OPTIONS['display']);
    }
}
