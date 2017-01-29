<?php
namespace Ridibooks\Cover;

use Ridibooks\Exception\RidiErrorException;

class ThumbnailGenerator
{
    const THUMB_TYPE_SCALED = 1;
    const THUMB_TYPE_DECORATED = 2;
    const THUMB_TYPE_ASPECT_FIT = 3;
    const THUMB_TYPE_SQUARE = 4;

    private $src;
    private $src_width;
    private $src_height;

    public function __construct($src)
    {
        if (!is_readable($src)) {
            throw new RidiErrorException('Cannot open source image: ' . $src);
        }

        $metadata = getimagesize($src);
        if ($metadata === false) {
            throw new RidiErrorException('Cannot read metadata: ' . $src);
        }

        if ($metadata['mime'] == 'image/jpeg') {
            $this->src = imagecreatefromjpeg($src);
        } elseif ($metadata['mime'] == 'image/png') {
            $this->src = imagecreatefrompng($src);
        } elseif ($metadata['mime'] == 'image/gif') {
            $this->src = imagecreatefromgif($src);
        } else {
            throw new RidiErrorException('Unsupported source image type: ' . $metadata['mime']);
        }

        $this->src_width = imagesx($this->src);
        $this->src_height = imagesy($this->src);
    }

    public function saveAsJpg($thumb_type, $width, $height, $path, $quality = 90)
    {
        $new_image = $this->generate($thumb_type, $width, $height);
        $output_path = $path . '.jpg';

        // 일단 리사이즈 후 quality 100으로 저장한 다음
        imagejpeg($new_image, $output_path, $quality);
        imagedestroy($new_image);

        // jpegoptim을 적용한다.
        exec('jpegoptim -p -q --strip-all ' . escapeshellarg(realpath($output_path)));

        return $output_path;
    }

    public function saveAsPng($thumb_type, $width, $height, $path)
    {
        $new_image = $this->generate($thumb_type, $width, $height);
        $output_path = $path . '.png';
        imagepng($new_image, $output_path);
        imagedestroy($new_image);

        // optipng는 시간이 너우 오래걸려 하지 않는다

        return $output_path;
    }

    private function generate($thumb_type, $width, $height)
    {
        if ($thumb_type == self::THUMB_TYPE_SCALED) {
            $new_image = $this->generateScaled($width, $height);
        } elseif ($thumb_type == self::THUMB_TYPE_ASPECT_FIT) {
            $new_image = $this->generateAspectFit($width, $height);
        } elseif ($thumb_type == self::THUMB_TYPE_SQUARE) {
            $new_image = $this->generateSquare($width);
        } else {
            throw new RidiErrorException('Illegal thumbnail type: ' . $thumb_type);
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
