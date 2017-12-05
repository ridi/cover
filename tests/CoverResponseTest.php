<?php
declare(strict_types=1);

namespace Ridibooks\Test\Cover;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cover\CoverOptions;
use Ridibooks\Cover\CoverResponse;
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
        ]);
    }

    /**
     * @param $size
     * @param $dpi
     * @param $format
     * @param $type
     * @dataProvider validArguments
     */
    public function testCreate($size, $dpi, $format, $type)
    {
        $b_id = '100000001';
        $display = 'lcd';

        $response = CoverResponse::create($b_id, $size, $dpi, $format, $type, $display);

        $this->assertInstanceOf(Response::class, $response);
    }
}
