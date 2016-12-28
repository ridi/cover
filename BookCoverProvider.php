<?php
namespace Ridibooks\Library\Cover;

use Ridibooks\Exception\RidiErrorException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BookCoverProvider
{
	private $b_id;
	private $cp_id;
	protected $width;
	protected $height;
	protected $subdirectory;

	public function __construct($b_id, $width, $height, $subdirectory)
	{
		$this->b_id = $b_id;
		$this->cp_id = substr($b_id, 0, -6);
		$this->width = $width;
		$this->height = $height;
		$this->subdirectory = $subdirectory;
	}

	abstract protected function getMIMEType();

	abstract protected function generate($output_file);

	public function provide()
	{
		if ($this->getSourcePath() === null) {
			return null;
		}

		$cached_cover = $this->getCachedPath();
		if ($this->isValid($cached_cover)) {
			return $cached_cover;
		}

		return $this->makeCache($cached_cover);
	}

	private function isValid($cached_cover_path)
	{
		if ($_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache') {
			return false;
		}

		if (file_exists($cached_cover_path)) {
			// 원본이 더 최신이면 Invalidate Cache
			if (filemtime($this->getSourcePath()) - filemtime($cached_cover_path) > 0) {
				@unlink($cached_cover_path);
			} else {
				return true;
			}
		}

		return false;
	}

	private function makeCache($output_file_path)
	{
		$tmp_filename = tempnam(\Env::$CACHE_BASE_DIR, 'cover_' . time());
		try {
			$new_cover_path = $this->generate($tmp_filename);

			@mkdir(dirname($output_file_path), 0755, true);
			if (rename($new_cover_path, $output_file_path)) {
				return $output_file_path;
			} else {
				throw new RidiErrorException('Failed to rename generated cover: ' . $new_cover_path . ' => ' . $output_file_path);
			}
		} catch (\Exception $e) {
			trigger_error('[COVER] Failed to create: ' . $e->getMessage());
		} finally {
			@unlink($tmp_filename);
		}

		return null;
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

	protected function getSourcePath()
	{
		$dir = \Env::$BOOK_DATA_BASE_DIR . '/' . $this->cp_id . '/' . $this->b_id;
		if (strlen($this->subdirectory) !== 0) {
			$dir .= '/' . $this->subdirectory;
		}

		$file = $dir . '/' . $this->b_id . '_org.jpg';
		if (is_readable($file)) {
			return $file;
		}

		return null;
	}

	private function getCachedPath()
	{
		return \Env::$CACHE_BASE_DIR . '/' . $this->cp_id . "/" . $this->b_id . '/' . $this->getCacheFilename();
	}

	abstract protected function getCacheFilename();
}
