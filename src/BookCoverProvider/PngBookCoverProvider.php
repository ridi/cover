<?php

namespace Ridibooks\Cover\BookCoverProvider;

class PngBookCoverProvider extends AbstractBookCoverProvider
{
    protected function getMIMEType()
    {
        return 'image/png';
    }

    protected function getCacheFilename()
    {
        $postfix = $this->getCacheFilenamePostfix();

        return sprintf('cover_%d%s.png', $this->cover_option_dto->width, $postfix);
    }

    protected function getExt()
    {
        return 'png';
    }

    protected function afterGenerate($new_image, $output_path)
    {
        imagepng($new_image, $output_path);

        // optipng는 시간이 너우 오래걸려 하지 않는다
    }
}
