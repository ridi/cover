<?php
declare(strict_types=1);

namespace Ridibooks\Cover\Options;

use Ridibooks\Cover\BookCoverProvider\JpgBookCoverProvider;
use Ridibooks\Cover\BookCoverProvider\PngBookCoverProvider;

class CoverOptions
{
    public const DEFAULT_KEY_SIZE = 'medium';
    public const DEFAULT_KEY_DPI = 'hdpi';
    public const DEFAULT_KEY_FORMAT = 'jpg';
    public const DEFAULT_KEY_TYPE = 'service';
    public const DEFAULT_KEY_DISPLAY = 'lcd';

    public const COLORSPACE_GRAYSCALE = 'grayscale';
    public const COLORSPACE_TRUECOLOR = 'truecolor';

    private const SUBDIRECTORY_NORMAL = '';
    private const SUBDIRECTORY_TEST = 'test';

    private static $OPTIONS = [
        'size' => [ // size => width
            'small' => 60,
            self::DEFAULT_KEY_SIZE => 80,
            'large' => 110,
            'xlarge' => 150,
            'xxlarge' => 320
        ],
        'dpi' => [ // dpi => scale
            'mdpi' => 1.0,
            self::DEFAULT_KEY_DPI => 1.5,
            'xhdpi' => 2.0,
            'xxhdpi' => 3.0
        ],
        'format' => [
            self::DEFAULT_KEY_FORMAT => JpgBookCoverProvider::class,
            'png' => PngBookCoverProvider::class,
        ],
        'type' => [ // service_type => subdirectory
            self::DEFAULT_KEY_TYPE => self::SUBDIRECTORY_NORMAL,
            'test' => self::SUBDIRECTORY_TEST
        ],
        'display' => [ // type => colorspace
            'epd' => self::COLORSPACE_GRAYSCALE,
            self::DEFAULT_KEY_DISPLAY => self::COLORSPACE_TRUECOLOR,
        ]
    ];

    private static function get($option, $key, $default_key)
    {
        $key = array_key_exists($key, self::$OPTIONS[$option]) ? $key : $default_key;

        return self::$OPTIONS[$option][$key];
    }

    public static function getWidth($size)
    {
        return self::get('size', $size, self::DEFAULT_KEY_SIZE);
    }

    public static function getScale($dpi): float
    {
        return self::get('dpi', $dpi, self::DEFAULT_KEY_DPI);
    }

    public static function getProviderClass($format): string
    {
        return self::get('format', $format, self::DEFAULT_KEY_FORMAT);
    }

    public static function getSubdirectory($service_type): string
    {
        return self::get('type', $service_type, self::DEFAULT_KEY_TYPE);
    }

    public static function getColorspace($display)
    {
        return self::get('display', $display, self::DEFAULT_KEY_DISPLAY);
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
