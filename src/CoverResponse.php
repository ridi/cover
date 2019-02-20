<?php
declare(strict_types=1);

namespace Ridibooks\Cover;

use Ridibooks\Cover\BookCoverProvider\AbstractBookCoverProvider;
use Ridibooks\Cover\FileProvider\AbstractFileProvider;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\Options\CoverOptions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CoverResponse
{
    /**
     * @param string $format
     * @param CoverOptionDto $cover_option_dto
     * @param AbstractFileProvider $file_provider
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public static function create($format, $cover_option_dto, $file_provider)
    {
        $class = CoverOptions::getProviderClass($format);
        /** @var AbstractBookCoverProvider $provider */
        $provider = new $class($cover_option_dto, $file_provider);

        $use_cache = true;
        if (Request::createFromGlobals()->isNoCache()) {
            $use_cache = false;
        }

        $thumb_path = $provider->provide($use_cache);
        if (!is_readable($thumb_path)) {
            return new Response('Cover not found.', Response::HTTP_NOT_FOUND);
        }

        BinaryFileResponse::trustXSendfileTypeHeader();
        $res = new BinaryFileResponse($thumb_path);
        $res->headers->set('Content-type', $provider->getMIMEType());
        if ($use_cache) {
            $res->setExpires(new \DateTime('+1 year'));
        }

        $res->prepare(Request::createFromGlobals());

        return $res;
    }
}
