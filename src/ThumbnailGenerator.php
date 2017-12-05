<?php
namespace Ridibooks\Cover;

use Ridibooks\Cover\Exception\CoverException;

class ThumbnailGenerator
{
    const THUMB_TYPE_SCALED = 1;
    const THUMB_TYPE_DECORATED = 2;
    const THUMB_TYPE_ASPECT_FIT = 3;
    const THUMB_TYPE_SQUARE = 4;

    private $src;
    private $src_width;
    private $src_height;

    private $thumb_type;

    public function __construct($src)
    {
        if (!is_readable($src)) {
            throw CoverException::fromInvalidSourceFile($src);
        }

        $metadata = getimagesize($src);
        if ($metadata === false) {
            throw CoverException::fromInvalidSourceFile($src);
        }

        if ($metadata['mime'] == 'image/jpeg') {
            $this->src = imagecreatefromjpeg($src);
        } elseif ($metadata['mime'] == 'image/png') {
            $this->src = imagecreatefrompng($src);
        } elseif ($metadata['mime'] == 'image/gif') {
            $this->src = imagecreatefromgif($src);
        } else {
            throw CoverException::fromInvalidMimeType($metadata['mime']);
        }

        $this->src_width = imagesx($this->src);
        $this->src_height = imagesy($this->src);

        $this->thumb_type = self::THUMB_TYPE_ASPECT_FIT;
    }

    public function save($width, $height, callable $post_processor)
    {
        $new_image = $this->generate($width, $height);
        $post_processor($new_image);

        imagedestroy($new_image);
    }

    private function generate($width, $height)
    {
        if ($this->thumb_type === self::THUMB_TYPE_SCALED) {
            $new_image = $this->generateScaled($width, $height);
        } elseif ($this->thumb_type === self::THUMB_TYPE_ASPECT_FIT) {
            $new_image = $this->generateAspectFit($width, $height);
        } elseif ($this->thumb_type === self::THUMB_TYPE_SQUARE) {
            $new_image = $this->generateSquare($width);
        } else {
            throw CoverException::fromInvalidThumbnailType($this->thumb_type);
        }

        return $new_image;
    }

    private function generateScaled($width, $height)
    {
        list($dst_w, $dst_h) = $this->getTargetImageSize($width, $height);

        $image = imagecreatetruecolor($dst_w, $dst_h);

        // 1. 원본 얹히기
        imagecopyresampled($image, $this->src, 0, 0, 0, 0, $dst_w, $dst_h, $this->src_width, $this->src_height);

        return $image;
    }

    private function generateAspectFit($width, $height)
    {
        if ($this->src_width > $width || $this->src_height > $height) {
            return $this->generateScaled($width, $height);
        }

        return $this->src;
    }

    private function getTargetImageSize($width, $height)
    {
        $scaled_width = $width;
        $scaled_height = $this->src_height * ($width / $this->src_width);

        if ($scaled_height > $height) {
            $resize_scale = $height / $scaled_height;

            $scaled_width = $scaled_width * $resize_scale;
            $scaled_height = $scaled_height * $resize_scale;
        }

        return [intval($scaled_width), intval($scaled_height)];
    }

    private function generateSquare($width)
    {
        $image = imagecreatetruecolor($width, $width);

        imagecopyresampled($image, $this->src, 0, 0, 0, 0, $width, $width, $this->src_width, $this->src_width);

        return $image;
    }

    public function dispose()
    {
        imagedestroy($this->src);
    }
}
