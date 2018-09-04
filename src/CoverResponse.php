<?php
namespace Ridibooks\Cover;

use Ridibooks\Cover\BookCoverProvider\AbstractBookCoverProvider;
use Ridibooks\Cover\FileProvider\AbstractFileProvider;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\Options\CoverOptions;

class CoverResponse
{
    /**
     * @param $format
     * @param CoverOptionDto $cover_option_dto
     * @param AbstractFileProvider $file_provider
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function create($format, $cover_option_dto, $file_provider)
    {
        $class = CoverOptions::getProviderClass($format);
        /** @var AbstractBookCoverProvider $provider */
        $provider = new $class($cover_option_dto, $file_provider);

        return $provider->getResponse();
    }
}
