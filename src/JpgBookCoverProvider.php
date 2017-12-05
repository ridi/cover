<?php
namespace Ridibooks\Cover;

class JpgBookCoverProvider extends BookCoverProvider
{
    private $quality_percent;

    public function __construct($b_id, $width, $height, $subdir, $quality_percent = 90)
    {
        parent::__construct($b_id, $width, $height, $subdir);
        $this->quality_percent = intval($quality_percent);
    }

    protected function getMIMEType()
    {
        return 'image/jpeg';
    }

    protected function getCacheFilename()
    {
        $postfix = empty($this->subdirectory) ? '' : '_' . $this->subdirectory;
        $postfix .= ($this->colorspace === CoverOptions::COLORSPACE_GRAYSCALE) ? '_d' : '';

        return sprintf('cover_%d_q%d%s.jpg', $this->width, $this->quality_percent, $postfix);
    }

    protected function generate($output_file)
    {
        $generator = new ThumbnailGenerator($this->getSourcePath());

        $output_path = $output_file . '.jpg';

        $generator->save($this->width, $this->height, $this->colorspace, function ($new_image) use ($output_path) {
            // 일단 리사이즈 후 quality 100으로 저장한 다음
            imagejpeg($new_image, $output_path, $this->quality_percent);

            // jpegoptim을 적용한다.
            exec('jpegoptim -p -q --strip-all ' . escapeshellarg(realpath($output_path)));
        });

        return $output_path;
    }
}
