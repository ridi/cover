<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

class Env
{
    public static $BOOK_DATA_BASE_DIR;
    public static $CACHE_BASE_DIR;
}

Env::$BOOK_DATA_BASE_DIR = __DIR__ . '/fixtures';
Env::$CACHE_BASE_DIR = __DIR__ . '/tmp';

// initialize
@mkdir(\Env::$CACHE_BASE_DIR);

// finalize
register_shutdown_function(
    function () {
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

        removeDir(\Env::$CACHE_BASE_DIR);
    }
);
