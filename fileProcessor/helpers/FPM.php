<?php
/**
 *
 */

namespace fileProcessor\helpers;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FPM
{
	/**
	 * @var \fileProcessor\components\ImageCache
	 */
	protected static $cache = null;

	/**
	 * @var \fileProcessor\components\FileTransfer
	 */
	protected static $transfer = null;

	/**
	 * @param string $module
	 *
	 * @throws \CException
	 * @return \fileProcessor\FileProcessorModule
	 */
	public static function m($module = 'file-processor')
	{
		if (!\Yii::app()->hasModule($module)) {
			throw new \CException('Wrong component name! You need call this method with right file-processor component name.');
		}
		return \Yii::app()->getModule($module);
	}

	public static function t($message, $category = '\fileProcessor\FileProcessorModule.core', $params = array(), $source = null, $language = null)
	{
		return \Yii::t($category, $message, $params, $source, $language);
	}

	/**
	 * @return \fileProcessor\components\ImageCache
	 */
	public static function cache()
	{
		if (is_null(self::$cache)) {
			self::$cache = \Yii::createComponent('\fileProcessor\components\ImageCache');
		}
		return self::$cache;
	}

	/**
	 * @return \fileProcessor\components\FileTransfer
	 */
	public static function transfer()
	{
		if (is_null(self::$transfer)) {
			self::$transfer = \Yii::createComponent('\fileProcessor\components\FileTransfer');
		}
		return self::$transfer;
	}

	/**
	 * Generates an image tag.
	 *
	 * @param integer $id          the image ID
	 * @param string  $moduleId    the module ID
	 * @param string  $size        size
	 * @param string  $alt         the alternative text display
	 * @param array   $htmlOptions additional HTML attributes (see {@link tag}).
	 *
	 * @internal param string $sectionId section ID
	 * @return string the generated image tag
	 */
	public static function image($id, $moduleId, $size, $alt = '', $htmlOptions = array())
	{
		if (!(int) $id) {
			return null;
		}
		return \CHtml::image(self::src($id, $moduleId, $size), $alt, $htmlOptions);
	}

	public static function originalImage($id, $alt = '', $htmlOptions = array())
	{
		if (!(int) $id) {
			return null;
		}
		return \CHtml::image(self::originalSrc($id), $alt, $htmlOptions);
	}

	/**
	 * Generates an image src.
	 *
	 * @param integer $id
	 * @param string  $model
	 * @param string  $size
	 *
	 * @internal param string $sectionId
	 * @return string
	 */
	public static function src($id, $model, $size)
	{
		if (!(int) $id) {
			return null;
		}

		$metaData = FPM::transfer()->getMetaData($id);
		$src = FPM::m()->host . FPM::m()->cachedImagesBaseDir . '/' . floor($id / FPM::m()->filesPerDir) . '/' . $model . '_' . $size . '/' . $id . '-' . $metaData['real_name'];

		return $src;
	}

	public static function originalSrc($id)
	{
		if (!(int) $id) {
			return null;
		}

		$metaData = FPM::transfer()->getMetaData($id);

		$src = FPM::m()->host . FPM::m()->originalBaseDir . '/' . floor($id / FPM::m()->filesPerDir) . '/' . $id . '.' . $metaData['extension'];

		return $src;
	}

	public static function getOriginalFilePath($id, $ext)
	{
		return FPM::getBasePath() . FPM::m()->originalBaseDir . DIRECTORY_SEPARATOR . floor($id / FPM::m()->filesPerDir) . DIRECTORY_SEPARATOR . $id . '.' . $ext;
	}

	public static function getOriginalFilePathById($id)
	{
		if (!(int) $id) {
			return false;
		}

		$info = self::transfer()->getMetaData($id);
		if (!$info) {
			return false;
		}
		return self::getOriginalFilePath($id, $info['extension']);
	}

	public static function getCachedImagePath($id, $model, $size, $fileName)
	{
		if (!(int) $id) {
			return false;
		}
		return FPM::getBasePath() . FPM::m()->cachedImagesBaseDir . DIRECTORY_SEPARATOR . floor($id / FPM::m()->filesPerDir) . DIRECTORY_SEPARATOR . $model . '_' . $size . DIRECTORY_SEPARATOR . $fileName;
	}

	public static function deleteFiles($fileId, $ext = false)
	{
		if (!(int) $fileId) {
			return null;
		}
		FPM::cache()->delete($fileId, $ext);
		FPM::transfer()->deleteFile($fileId, $ext);
	}

	public static function getBasePath()
	{
		return FPM::m()->baseDir ? FPM::m()->baseDir : \Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
	}
}
