<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Cover;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cover\PngBookCoverProvider;

class BookCoverProviderTest extends TestCase
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

        $this->assertFileExists($cover_path);
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

        $this->assertNull($cover_path);
    }
}
