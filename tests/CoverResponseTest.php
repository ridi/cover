<?php
declare(strict_types=1);

namespace Ridibooks\Test\Cover;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\Options\CoverOptions;
use Ridibooks\Cover\CoverResponse;
use Ridibooks\Tests\Cover\TestFileProvider;
use Symfony\Component\HttpFoundation\Response;

class CoverResponseTest extends TestCase
{
    function arrayWalk(array $it_array): \Generator
    {
        $head = $it_array[0];
        $tail = array_slice($it_array, 1);

        if (empty($tail)) {
            foreach ($head as $h) {
                yield [$h];
            }
        } else {
            foreach ($head as $h) {
                foreach ($this->arrayWalk($tail) as $t) {
                    $r = array_merge([$h], $t);
                    yield $r;
                }
            }
        }
    }

    public function validArguments()
    {
        return $this->arrayWalk([
            CoverOptions::getAvailableSizes(),
            CoverOptions::getAvailableDpis(),
            CoverOptions::getAvailableFormats(),
            CoverOptions::getAvailableTypes(),
            CoverOptions::getAvailableDisplays(),
        ]);
    }

    /**
     * @param $size
     * @param $dpi
     * @param $format
     * @param $type
     * @dataProvider validArguments
     */
    public function testCreate($size, $dpi, $format, $type, $display)
    {
        $b_id = '100000001';
        $cover_option_dto = CoverOptionDto::import($b_id, $size, $dpi, $type, $display);
        $file_provider = new TestFileProvider();

        $response = CoverResponse::create($format, $cover_option_dto, $file_provider);

        $this->assertInstanceOf(Response::class, $response);
    }
}
