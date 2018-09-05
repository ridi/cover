<?php
declare(strict_types=1);

namespace Ridibooks\Tests\Cover;

use Ridibooks\Cover\FileProvider\AbstractFileProvider;

class TestFileProvider extends AbstractFileProvider
{
    /* public for test */
    public function getCacheFilePath()
    {
        return __DIR__ . '/tmp';
    }

    protected function getBookDataPath()
    {
        return __DIR__ . '/fixtures';
    }
}
