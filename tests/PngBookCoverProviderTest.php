<?php

namespace Ridibooks\Tests\Cover;

use Ridibooks\Cover\PngBookCoverProvider;

class BookCoverProviderTest extends \PHPUnit_Framework_TestCase
{
    public function serviceTypes(): array
    {
        return [[''], ['test']];
    }

    /**
     * @dataProvider serviceTypes
     */
    public function testPngProvider($subdirectory)
    {
        $b_id = '100000001';
        $width = 1080;
        $height = 1920;

        $provider = new PngBookCoverProvider($b_id, $width, $height, $subdirectory);
        $cover_path = $provider->provide();

        self::assertFileExists($cover_path);
    }

    /**
     * @dataProvider serviceTypes
     */
    public function testPngProviderWithInvalidCover($subdirectory)
    {
        $b_id = '-1';
        $width = 1080;
        $height = 1920;

        $provider = new PngBookCoverProvider($b_id, $width, $height, $subdirectory);
        $cover_path = $provider->provide();

        self::assertNull($cover_path);
    }
}
