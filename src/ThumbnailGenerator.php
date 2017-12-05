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

        $this->thumb_type = self::THUMB_TYPE_ASPECT_FIT;
    }

    private function getSrcWidth()
    {
        return imagesx($this->src);
    }

    private function getSrcHeight()
    {
        return imagesy($this->src);
    }

    public function setThumbType($type)
    {
        $this->thumb_type = $type;
    }

    public function save($width, $height, $colorspace, callable $post_processor)
    {
        $new_image = $this->generate($width, $height);

        // 흑백으로 출력할 경우 디더링 적용
        if ($colorspace === CoverOptions::COLORSPACE_GRAYSCALE) {
            $this->ditherGray16($new_image);
        }

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

    /**
     * EPD에 최적화된 디더링 적용
     *
     * @param resource $im
     */
    private function ditherGray16($im)
    {
        $levels = 16;

        // 디테일이 없어짐
        //imagefilter($im, IMG_FILTER_GRAYSCALE);
        imagetruecolortopalette($im, true, 256);

        $num_colors = imagecolorstotal($im);

        for ($c = 0; $c < $num_colors; $c++) {
            $col = imagecolorsforindex($im, $c);
            $gray = round(0.299 * $col['red'] + 0.587 * $col['green'] + 0.114 * $col['blue']);

            $l = intval($gray / $levels);
            $g = $l * $levels + $l;
            imagecolorset($im, $c, $g, $g, $g);
        }
    }

    private function generateScaled($width, $height)
    {
        list($dst_w, $dst_h) = $this->getTargetImageSize($width, $height);

        $image = imagecreatetruecolor($dst_w, $dst_h);

        // 1. 원본 얹히기
        imagecopyresampled($image, $this->src, 0, 0, 0, 0, $dst_w, $dst_h, $this->getSrcWidth(), $this->getSrcHeight());

        return $image;
    }

    private function generateAspectFit($width, $height)
    {
        if ($this->getSrcWidth() > $width || $this->getSrcHeight() > $height) {
            return $this->generateScaled($width, $height);
        }

        return $this->src;
    }

    private function getTargetImageSize($width, $height)
    {
        $scaled_width = $width;
        $scaled_height = $this->getSrcHeight() * ($width / $this->getSrcWidth());

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

        imagecopyresampled($image, $this->src, 0, 0, 0, 0, $width, $width, $this->getSrcWidth(), $this->getSrcWidth());

        return $image;
    }

    public function dispose()
    {
        imagedestroy($this->src);
    }
}
