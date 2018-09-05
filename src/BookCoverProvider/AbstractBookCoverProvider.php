<?php
namespace Ridibooks\Cover\BookCoverProvider;

use Ridibooks\Cover\Exception\CoverException;
use Ridibooks\Cover\FileProvider\AbstractFileProvider;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\Options\CoverOptions;
use Ridibooks\Cover\ThumbnailGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractBookCoverProvider
{
    /** @var CoverOptionDto */
    protected $cover_option_dto;
    /** @var AbstractFileProvider */
    protected $file_provider;

    public function __construct($cover_option_dto, $file_provider)
    {
        $this->cover_option_dto = $cover_option_dto;
        $this->file_provider = $file_provider;
    }

    /**
     * @param bool $enable_cache
     * @return Response
     */
    public function getResponse($enable_cache = true)
    {
        $thumb_path = $this->provide();
        if (!is_readable($thumb_path)) {
            return new Response('Cover not found.', Response::HTTP_NOT_FOUND);
        }

        BinaryFileResponse::trustXSendfileTypeHeader();
        $res = new BinaryFileResponse($thumb_path);
        $res->headers->set('Content-type', $this->getMIMEType());

        if ($enable_cache) {
            $res->setExpires(new \DateTime('+3 months'));
        }

        $res->prepare(Request::createFromGlobals());

        return $res;
    }

    abstract protected function getMIMEType();

    /* public for test */
    public function provide()
    {
        $source_path = $this->getSourcePath();

        if ($source_path === null) {
            return null;
        }

        $cached_cover = $this->file_provider->getCachedPath(
            $this->cover_option_dto->cp_id,
            $this->cover_option_dto->b_id,
            $this->getCacheFilename()
        );

        if ($this->isValid($cached_cover)) {
            return $cached_cover;
        }

        return $this->makeCache($cached_cover);
    }

    private function getSourcePath()
    {
        $source_path = $this->file_provider->getSourcePath(
            $this->cover_option_dto->cp_id,
            $this->cover_option_dto->b_id,
            $this->cover_option_dto->sub_dir
        );

        if (is_readable($source_path)) {
            return $source_path;
        }

        return null;
    }

    abstract protected function getCacheFilename();

    private function isValid($cached_cover_path)
    {
        if (Request::createFromGlobals()->isNoCache()) {
            return false;
        }

        if (file_exists($cached_cover_path)) {
            // 원본이 더 최신이면 Invalidate Cache
            $source_path = $this->getSourcePath();
            if (filemtime($source_path) - filemtime($cached_cover_path) > 0) {
                @unlink($cached_cover_path);
            } else {
                return true;
            }
        }

        return false;
    }

    private function makeCache($output_file_path)
    {
        $tmp_filename = $this->file_provider->getTempFilePath();
        try {
            $new_cover_path = $this->generate($tmp_filename);

            @mkdir(dirname($output_file_path), 0755, true);
            if (rename($new_cover_path, $output_file_path)) {
                return $output_file_path;
            }

            throw CoverException::fromNonCacheableOutput($new_cover_path, $output_file_path);
        } catch (\Exception $e) {
            trigger_error('[COVER] Failed to create: ' . $e->getMessage());
        } finally {
            @unlink($tmp_filename);
        }

        return null;
    }

    private function generate($output_file)
    {
        $source_path = $this->getSourcePath();
        $generator = new ThumbnailGenerator($source_path);

        $output_path = $output_file . '.' . $this->getExt();

        $width = $this->cover_option_dto->width;
        $height = $this->cover_option_dto->height;
        $color_space = $this->cover_option_dto->color_space;

        $generator->save($width, $height, $color_space, function ($new_image) use ($output_path) {
            $this->afterGenerate($new_image, $output_path);
        });

        return $output_path;
    }

    abstract protected function getExt();

    abstract protected function afterGenerate($new_image, $output_path);

    protected function getCacheFilenamePostfix()
    {
        $postfix = empty($this->cover_option_dto->sub_dir) ? '' : '_' . $this->cover_option_dto->sub_dir;
        $postfix .= ($this->cover_option_dto->color_space === CoverOptions::COLORSPACE_GRAYSCALE) ? '_d' : '';

        return $postfix;
    }
}
