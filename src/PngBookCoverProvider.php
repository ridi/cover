<?php
namespace Ridibooks\Cover;

class PngBookCoverProvider extends BookCoverProvider
{
    protected function getMIMEType()
    {
        return 'image/png';
    }

    protected function getCacheFilename()
    {
        $postfix = empty($this->subdirectory) ? '' : '_' . $this->subdirectory;

        return sprintf('cover_%d%s.png', $this->width, $postfix);
    }

    protected function generate($output_file)
    {
        $generator = new ThumbnailGenerator($this->getSourcePath());

        $output_path = $output_file . '.png';

        $generator->save($this->width, $this->height, function ($new_image) use ($output_path) {
            imagepng($new_image, $output_path);

            // optipng는 시간이 너우 오래걸려 하지 않는다
        });

        return $output_path;
    }
}
