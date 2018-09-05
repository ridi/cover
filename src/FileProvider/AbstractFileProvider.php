<?php

namespace Ridibooks\Cover\FileProvider;

abstract class AbstractFileProvider
{
    /* public for test */
    abstract public function getCacheFilePath();

    abstract protected function getBookDataPath();

    public function getTempFilePath()
    {
        return tempnam($this->getCacheFilePath(), 'cover_' . time());
    }

    public function getSourcePath($cp_id, $b_id, $sub_dir)
    {
        $dir = $this->getBookDataPath() . "/{$cp_id}/{$b_id}";
        if ($sub_dir !== '') {
            $dir .= '/' . $sub_dir;
        }

        return $dir . '/' . $b_id . '_org.jpg';
    }

    public function getCachedPath($cp_id, $b_id, $cache_filename)
    {
        return $this->getCacheFilePath() . "/{$cp_id}/{$b_id}/{$cache_filename}";
    }
}
