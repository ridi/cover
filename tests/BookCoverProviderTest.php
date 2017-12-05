<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Cover;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cover\CoverOptions;
use Ridibooks\Cover\JpgBookCoverProvider;
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

    public function testDitheredGrayscale()
    {
        $b_id = '100000001';
        $width = 1080;
        $height = 1920;
        $subdir = '';

        $provider = new JpgBookCoverProvider($b_id, $width, $height, $subdir);
        $provider->setColorspace(CoverOptions::COLORSPACE_GRAYSCALE);

        $output = $provider->provide();

        // Validate if image is grayscale.
        $im = imagecreatefromjpeg($output);
        imagetruecolortopalette($im, false, 16);
        for ($i = 0; $i < imagecolorstotal($im); $i++) {
            $color = imagecolorsforindex($im, $i);

            // R = G = B 인 경우 gray 라고 판단해야 하지만, Green은 jitter를 가지는 경우가 있어서 R = B만 비교
            $this->assertEquals($color['red'], $color['blue']);
        }
    }
}
