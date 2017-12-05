<?php
declare(strict_types=1);

namespace Ridibooks\Cover\Exception;

class CoverException extends \RuntimeException
{
    public static function fromInvalidSourceFile(string $src): self
    {
        return new self('Cannot open source image: ' . $src);
    }

    public static function fromInvalidMimeType($mime): self
    {
        return new self('Unsupported source image type: ' . $mime);
    }

    public static function fromInvalidThumbnailType($thumb_type): self
    {
        return new self('Illegal thumbnail type: ' . $thumb_type);
    }

    public static function fromNonCacheableOutput(string $new_cover_path, string $output_file_path): self
    {
        return new self('Failed to cache generated cover: ' . $new_cover_path . ' => ' . $output_file_path);
    }
}
