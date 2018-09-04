<?php

namespace Ridibooks\Cover\BookCoverProvider;

class JpgBookCoverProvider extends AbstractBookCoverProvider
{
    private $quality_percent;

    public function __construct($cover_option_dto, $file_provider, $quality_percent = 90)
    {
        parent::__construct($cover_option_dto, $file_provider);
        $this->quality_percent = (int)$quality_percent;
    }

    protected function getMIMEType()
    {
        return 'image/jpeg';
    }

    protected function getCacheFilename()
    {
        $postfix = $this->getCacheFilenamePostfix();

        return sprintf('cover_%d_q%d%s.jpg', $this->cover_option_dto->width, $this->quality_percent, $postfix);
    }

    protected function getExt()
    {
        return 'jpg';
    }

    protected function afterGenerate($new_image, $output_path)
    {
        // 일단 리사이즈 후 quality 100으로 저장한 다음
        imagejpeg($new_image, $output_path, $this->quality_percent);

        // jpegoptim을 적용한다.
        exec('jpegoptim -p -q --strip-all ' . escapeshellarg(realpath($output_path)));
    }
}
