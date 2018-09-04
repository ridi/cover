<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

// initialize
// equal TestFileProvider::getCacheFilePath
$cache_base_dir = __DIR__ . '/tmp';
if (!mkdir($cache_base_dir) && !is_dir($cache_base_dir)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $cache_base_dir));
}

// finalize
register_shutdown_function(
    function () use ($cache_base_dir) {
        function removeDir($target)
        {
            $fp = @opendir($target);
            if (!$fp) {
                return;
            }

            while (false !== ($file = readdir($fp))) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }

                if (is_dir($target . '/' . $file)) {
                    removeDir($target . '/' . $file);
                } else {
                    @unlink($target . '/' . $file);
                }
            }
            closedir($fp);
            rmdir($target);
        }

        removeDir($cache_base_dir);
    }
);
