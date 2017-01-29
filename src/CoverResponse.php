<?php
namespace Ridibooks\Library\Cover;

class CoverResponse
{
	private static $OPTIONS = [
		'size' => [
			'small' => 60,
			'medium' => 80,
			'large' => 110,
			'xlarge' => 150,
			'xxlarge' => 320
		],
		'dpi' => [
			'mdpi' => 1.0,
			'hdpi' => 1.5,
			'xhdpi' => 2.0,
			'xxhdpi' => 3.0
		],
		'format' => [
			'jpg' => JpgBookCoverProvider::class,
			'png' => PngBookCoverProvider::class
		],
		'type' => [
			'service' => '',
			'test' => 'test'
		]
	];

	private static function getOption($option, $key, $default_key)
	{
		$key = array_key_exists($key, self::$OPTIONS[$option]) ? $key : $default_key;

		return self::$OPTIONS[$option][$key];
	}

	/**
	 * @param $b_id
	 * @param $size
	 * @param $dpi
	 * @param $format
	 * @param $type
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function create($b_id, $size, $dpi, $format, $type)
	{
		$width = self::getOption('size', $size, 'medium');
		$scale = self::getOption('dpi', $dpi, 'hdpi');
		$class = self::getOption('format', $format, 'jpg');
		$sub_dir = self::getOption('type', $type, 'service');

		$width = intval($width * $scale);
		$height = 10000;

		/** @var BookCoverProvider $provider */
		$provider = new $class($b_id, $width, $height, $sub_dir);

		return $provider->getResponse();
	}

	/**
	 * @return array
	 */
	public static function getAvailableSizes()
	{
		return array_keys(self::$OPTIONS['size']);
	}
}
