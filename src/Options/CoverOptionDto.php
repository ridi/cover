<?php
namespace Ridibooks\Cover\Options;

class CoverOptionDto
{
    public $b_id;
    public $cp_id;
    public $width;
    public $height;
    public $sub_dir;
    public $color_space;

    private function __construct($b_id, $width, $height, $sub_dir, $color_space)
    {
        $this->b_id = $b_id;
        $this->width = $width;
        $this->height = $height;
        $this->sub_dir = $sub_dir;
        $this->color_space = $color_space;
        $this->cp_id = substr($b_id, 0, -6);
    }

    public static function import($b_id, $size, $dpi, $type, $display)
    {
        $width = CoverOptions::getWidth($size);
        $scale = CoverOptions::getScale($dpi);
        $sub_dir = CoverOptions::getSubdirectory($type);
        $color_space = CoverOptions::getColorspace($display);

        $width = (int)($width * $scale);
        $height = 10000;

        return new self($b_id, $width, $height, $sub_dir, $color_space);
    }
}
