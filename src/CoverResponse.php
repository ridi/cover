<?php
namespace Ridibooks\Cover;

class CoverResponse
{
    /**
     * @param $b_id
     * @param $size
     * @param $dpi
     * @param $format
     * @param $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function create($b_id, $size, $dpi, $format, $type, $display)
    {
        $width = CoverOptions::getWidth($size);
        $scale = CoverOptions::getScale($dpi);
        $class = CoverOptions::getProviderClass($format);
        $sub_dir = CoverOptions::getSubdirectory($type);
        $colorspace = CoverOptions::getColorspace($display);

        $width = intval($width * $scale);
        $height = 10000;

        /** @var BookCoverProvider $provider */
        $provider = new $class($b_id, $width, $height, $sub_dir);
        $provider->setColorspace($colorspace);

        return $provider->getResponse();
    }
}
