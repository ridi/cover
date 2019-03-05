<?php
declare(strict_types=1);

namespace Ridibooks\Cover\BookCoverProvider;

use Ridibooks\Cover\Exception\CoverException;
use Ridibooks\Cover\FileProvider\AbstractFileProvider;
use Ridibooks\Cover\Options\CoverOptionDto;
use Ridibooks\Cover\Options\CoverOptions;
use Ridibooks\Cover\ThumbnailGenerator;

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

    abstract public function getMIMEType();

    /* public for test */
    public function provide($use_cache = true): ?string
    {
        $source_path = $this->getSourcePath();

        if ($source_path === null) {
            return null;
        }

        $cached_cover_path = $this->file_provider->getCachedPath(
            $this->cover_option_dto->cp_id,
            $this->cover_option_dto->b_id,
            $this->getCacheFilename()
        );

        if ($use_cache && $this->isValid($cached_cover_path)) {
            return $cached_cover_path;
        }

        return $this->makeCache($cached_cover_path);
    }

    private function getSourcePath(): ?string
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

    private function isValid($cached_cover_path): bool
    {
        if (!file_exists($cached_cover_path)) {
            return false;
        }

        // 원본이 더 최신이면 Invalidate Cache
        $source_path = $this->getSourcePath();
        if (filemtime($source_path) - filemtime($cached_cover_path) > 0) {
            @unlink($cached_cover_path);

            return false;
        }

        return true;
    }

    private function makeCache($output_file_path): ?string
    {
        $tmp_filename = $this->file_provider->getTempFilePath();
        try {
            $this->generate($tmp_filename);

            @mkdir(dirname($output_file_path), 0755, true);
            if (rename($tmp_filename, $output_file_path)) {
                return $output_file_path;
            }

            throw CoverException::fromNonCacheableOutput($tmp_filename, $output_file_path);
        } catch (\Exception $e) {
            trigger_error('[COVER] Failed to create: ' . $e->getMessage());
        } finally {
            @unlink($tmp_filename);
        }

        return null;
    }

    private function generate($tmp_file_path)
    {
        $source_path = $this->getSourcePath();
        $generator = new ThumbnailGenerator($source_path);

        $width = $this->cover_option_dto->width;
        $height = $this->cover_option_dto->height;
        $color_space = $this->cover_option_dto->color_space;

        $generator->save($width, $height, $color_space, function ($new_image) use ($tmp_file_path) {
            $this->afterGenerate($new_image, $tmp_file_path);
        });
    }

    abstract protected function afterGenerate($new_image, $output_path);

    protected function getCacheFilenamePostfix(): string
    {
        $postfix = empty($this->cover_option_dto->sub_dir) ? '' : '_' . $this->cover_option_dto->sub_dir;
        $postfix .= ($this->cover_option_dto->color_space === CoverOptions::COLORSPACE_GRAYSCALE) ? '_d' : '';

        return $postfix;
    }
}
