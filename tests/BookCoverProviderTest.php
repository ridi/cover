<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Cover;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\BookCoverProvider\JpgBookCoverProvider;
use Ridibooks\Cover\BookCoverProvider\PngBookCoverProvider;

class BookCoverProviderTest extends TestCase
{
    public function serviceTypes(): array
    {
        return [[''], ['test']];
    }

    /**
     * @dataProvider serviceTypes
     */
    public function testPngProvider($service_type)
    {
        $b_id = '100000001';
        $size = 'xxlarge';
        $dpi = 'xxhdpi';
        $display = 'lcd';
        $cover_option_dto = CoverOptionDto::import($b_id, $size, $dpi, $service_type, $display);
        $file_provider = new TestFileProvider();

        $provider = new PngBookCoverProvider($cover_option_dto, $file_provider);
        $cover_path = $provider->provide();

        $this->assertFileExists($cover_path);
    }

    /**
     * @dataProvider serviceTypes
     */
    public function testPngProviderWithInvalidCover($service_type)
    {
        $b_id = '-1';
        $size = 'xxlarge';
        $dpi = 'xxhdpi';
        $display = 'lcd';
        $cover_option_dto = CoverOptionDto::import($b_id, $size, $dpi, $service_type, $display);
        $file_provider = new TestFileProvider();

        $provider = new PngBookCoverProvider($cover_option_dto, $file_provider);
        $cover_path = $provider->provide();

        $this->assertNull($cover_path);
    }

    public function testDitheredGrayscale()
    {
        $b_id = '100000001';
        $size = 'xxlarge';
        $dpi = 'xxhdpi';
        $service_type = '';
        $display = 'epd';
        $cover_option_dto = CoverOptionDto::import($b_id, $size, $dpi, $service_type, $display);
        $file_provider = new TestFileProvider();

        $provider = new JpgBookCoverProvider($cover_option_dto, $file_provider);

        $output = $provider->provide();

        // Validate if image is grayscale.
        $im = imagecreatefromjpeg($output);
        imagetruecolortopalette($im, false, 16);
        $colors = imagecolorstotal($im);
        for ($i = 0; $i < $colors; $i++) {
            $color = imagecolorsforindex($im, $i);

            // R = G = B 인 경우 gray 라고 판단해야 하지만, Green은 jitter를 가지는 경우가 있어서 R = B만 비교
            $this->assertEquals($color['red'], $color['blue']);
        }
    }
}
