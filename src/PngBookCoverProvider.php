<?php
namespace Ridibooks\Library\Cover;

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

		return $generator->saveAsPng(ThumbnailGenerator::THUMB_TYPE_ASPECT_FIT, $this->width, $this->height, $output_file);
	}
}
