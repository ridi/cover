<?php
namespace Ridibooks\Library\Cover;

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

		return sprintf('cover_%d_q%d%s.jpg', $this->width, $this->quality_percent, $postfix);
	}

	protected function generate($output_file)
	{
		$generator = new ThumbnailGenerator($this->getSourcePath());

		return $generator->saveAsJpg(ThumbnailGenerator::THUMB_TYPE_ASPECT_FIT, $this->width, $this->height, $output_file, $this->quality_percent);
	}
}
