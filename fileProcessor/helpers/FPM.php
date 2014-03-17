<?php
/**
 *
 */

namespace fileProcessor\helpers;

use CException;
use CHtml;
use fileProcessor\components\FileTransfer;
use fileProcessor\components\ImageCache;
use fileProcessor\FileProcessorModule;
use Yii;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FPM
{
	/**
	 * @var ImageCache
	 */
	protected static $cache = null;

	/**
	 * @var FileTransfer
	 */
	protected static $transfer = null;

	/**
	 * @param string $module
	 *
	 * @throws CException
	 * @return FileProcessorModule
	 */
	public static function m($module = 'file-processor')
	{
		if (!Yii::app()->hasModule($module)) {
			throw new CException('Wrong component name! You need call this method with right file-processor component name.');
		}
		return Yii::app()->getModule($module);
	}

	/**
	 * @return ImageCache
	 */
	public static function cache()
	{
		if (is_null(self::$cache)) {
			self::$cache = Yii::createComponent('\fileProcessor\components\ImageCache');
		}

		return self::$cache;
	}

	/**
	 * @return FileTransfer
	 */
	public static function transfer()
	{
		if (is_null(self::$transfer)) {
			self::$transfer = Yii::createComponent('\fileProcessor\components\FileTransfer');
		}

		return self::$transfer;
	}

	/**
	 * Generates an image tag.
	 *
	 * @param integer $id the image ID
	 * @param string $moduleId the module ID
	 * @param string $size size
	 * @param string $alt the alternative text display
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 *
	 * @internal param string $sectionId section ID
	 * @return string the generated image tag
	 */
	public static function image($id, $moduleId, $size, $alt = '', $htmlOptions = array())
	{
		if (!(int)$id) {
			return null;
		}

		return CHtml::image(self::src($id, $moduleId, $size), $alt, $htmlOptions);
	}

	/**
	 * @param $id
	 * @param string $alt
	 * @param array $htmlOptions
	 *
	 * @return null|string
	 */
	public static function originalImage($id, $alt = '', $htmlOptions = array())
	{
		if (!(int)$id) {
			return null;
		}

		return CHtml::image(self::originalSrc($id), $alt, $htmlOptions);
	}

	/**
	 * Generates an image src.
	 *
	 * @param integer $id
	 * @param string $model
	 * @param string $size
	 *
	 * @internal param string $sectionId
	 * @return string
	 */
	public static function src($id, $model, $size)
	{
		if (!(int)$id) {
			return null;
		}

		$metaData = FPM::transfer()->getMetaData($id);
		$ext = $metaData['extension'] ? '.' . $metaData['extension'] : null;
		$src = FPM::m()->host . FPM::m()->cachedImagesBaseDir . '/' . floor(
				$id / FPM::m()->filesPerDir
			) . '/' . $model . '_' . $size . '/' . rawurlencode($id . '-' . $metaData['real_name'] . $ext);

		return $src;
	}

	/**
	 * @param $id
	 *
	 * @return null|string
	 */
	public static function originalSrc($id)
	{
		if (!(int)$id) {
			return null;
		}

		$metaData = FPM::transfer()->getMetaData($id);
		$ext = $metaData['extension'] ? '.' . $metaData['extension'] : null;
		$src = FPM::m()->host . FPM::m()->originalBaseDir . '/' . floor(
				$id / FPM::m()->filesPerDir
			) . '/' . rawurlencode($id . '-' . $metaData['real_name'] . $ext);

		return $src;
	}

	/**
	 * @param $id
	 * @param $fileName
	 * @param $ext
	 *
	 * @return string
	 */
	public static function getOriginalFilePath($id, $fileName, $ext)
	{
		$ext = $ext ? '.' . $ext : null;
		return FPM::getBasePath() . FPM::m()->originalBaseDir . DIRECTORY_SEPARATOR . floor(
			$id / FPM::m()->filesPerDir
		) . DIRECTORY_SEPARATOR . rawurlencode($id . '-' . $fileName . $ext);
	}

	/**
	 * @param $id
	 *
	 * @return bool|string
	 */
	public static function getOriginalFilePathById($id)
	{
		if (!(int)$id) {
			return false;
		}

		$meta = self::transfer()->getMetaData($id);
		if (!$meta) {
			return false;
		}

		return self::getOriginalFilePath($id, $meta['real_name'], $meta['extension']);
	}

	/**
	 * @param $id
	 * @param $model
	 * @param $size
	 * @param $file
	 *
	 * @return bool|string
	 */
	public static function getCachedImagePath($id, $model, $size, $file)
	{
		if (!(int)$id) {
			return false;
		}

		return FPM::getBasePath() . FPM::m()->cachedImagesBaseDir . DIRECTORY_SEPARATOR . floor(
			$id / FPM::m()->filesPerDir
		) . DIRECTORY_SEPARATOR . $model . '_' . $size . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * @param $id
	 *
	 * @return null
	 */
	public static function deleteFiles($id)
	{
		if (!(int)$id) {
			return null;
		}
		FPM::cache()->delete($id);
		FPM::transfer()->deleteFile($id);
		return true;
	}

	/**
	 * @return bool|string
	 */
	public static function getBasePath()
	{
		return FPM::m()->baseDir ? FPM::m()->baseDir
			: Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
	}

	/**
	 * @param $id
	 * @param $model
	 * @param $type
	 *
	 * @throws CException
	 */
	public static function createCacheDir($id, $model, $type)
	{
		$dirName = FPM::getBasePath() . FPM::m()->cachedImagesBaseDir . DIRECTORY_SEPARATOR . floor(
				$id / FPM::m()->filesPerDir
			) . DIRECTORY_SEPARATOR . $model . '_' . $type;
		if (!is_dir($dirName)) {
			FPM::mkdir($dirName, 0777, true);
		}
	}

	/**
	 * Shared environment safe version of mkdir. Supports recursive creation.
	 * For avoidance of umask side-effects chmod is used.
	 *
	 * @static
	 *
	 * @param string $dst path to be created
	 * @param int $mode
	 * @param boolean $recursive
	 *
	 * @throws CException
	 * @return boolean result of mkdir
	 * @see mkdir
	 */
	public static function mkdir($dst, $mode = 0777, $recursive = false)
	{
		$prevDir = \dirname($dst);
		if ($recursive && !is_dir($dst) && !is_dir($prevDir)) {
			self::mkdir(\dirname($dst), $mode, true);
		}
		if (!is_writable($prevDir)) {
			$message = 'Can not create directory: {dir} <br>Directory {prev} is not writable.';
			$message = strtr($message, array('{dir}' => $dst, '{prev}' => $prevDir, ));
			throw new CException($message);
		}
		$res = \mkdir($dst, $mode);
		\chmod($dst, $mode);

		return $res;
	}
}
