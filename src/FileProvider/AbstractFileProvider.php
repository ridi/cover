<?php
declare(strict_types=1);

namespace Ridibooks\Cover\FileProvider;

use Ridibooks\Cover\Options\CoverOptionDto;

abstract class AbstractFileProvider
{
    /** @var CoverOptionDto */
    protected $cover_option_dto;

    public function __construct($cover_option_dto)
    {
        $this->cover_option_dto = $cover_option_dto;
    }

    abstract protected function getCacheFilePath();

    abstract protected function getBookDataPath();

    public function getTempFilePath()
    {
        return tempnam($this->getCacheFilePath(), 'cover_' . time());
    }

    public function getSourcePath()
    {
        $cover_option_dto = $this->cover_option_dto;
        $dir = $this->getBookDataPath() . "/{$cover_option_dto->cp_id}/{$cover_option_dto->b_id}";
        if ($cover_option_dto->sub_dir !== '') {
            $dir .= '/' . $cover_option_dto->sub_dir;
        }

        $file = $dir . '/' . $cover_option_dto->b_id . '_org.jpg';
        if (is_readable($file)) {
            return $file;
        }

        return null;
    }

    public function getCachedPath($cache_filename)
    {
        $cover_option_dto = $this->cover_option_dto;

        return $this->getCacheFilePath() . "/{$cover_option_dto->cp_id}/{$cover_option_dto->b_id}/{$cache_filename}";
    }
}
