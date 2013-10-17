<?php
/**
 *
 */

namespace fileProcessor\controllers;

use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class ImageController extends \CController
{
	public $layout = false;

	public function init()
	{
		parent::init();
		\Yii::app()->errorHandler->errorAction = $this->route . '/error';
	}

	public function actionResize($model, $type, $id, $fileName, $ext)
	{
		$file = FPM::getOriginalFilePath($id, $ext);
		if (file_exists($file)) {
			$meta = FPM::transfer()->getMetaData($id);
			if (!(is_array($meta) && isset($meta['real_name']) && $fileName . '.' . $ext === $meta['real_name'])) {
				throw new \CHttpException(404, FPM::t('File not found'));
			}
			/** @var $ih \fileProcessor\extensions\imageHandler\drivers\MDriverAbstract|\fileProcessor\extensions\imageHandler\MImageHandler */
			$ih = \Yii::createComponent(FPM::m()->imageHandler);
			$ih->init();
			$config = isset(FPM::m()->imageSections[$model]) && isset(FPM::m()->imageSections[$model][$type]) ? FPM::m()->imageSections[$model][$type] : null;
			if (!$config) {
				throw new \CHttpException(400, FPM::t('Incorrect request'));
			}
			$thumbFile = FPM::getCachedImagePath($id, $model, $type, $id . '-' . $meta['real_name']);

			$this->createCacheDir($id, $model, $type);

			$ih->load($file);
			if (isset($config['do'])) {
				switch($config['do'])
				{
					case 'adaptiveThumb':
						$ih->adaptiveThumb($config['width'], $config['height']);
						break;
					case 'resize':
						$ih->resize($config['width'], $config['height']);
						break;
					case 'thumb':
						$ih->thumb($config['width'], $config['height']);
						break;
					default:
						break;
				}
			} else {
				$ih->adaptiveThumb($config['width'], $config['height']);
			}
			$ih->save($thumbFile, false, $config['quality']);
			$ih->show(false, $config['quality']);
		} else {
			throw new \CHttpException(404, FPM::t('File not found'));
		}
		\Yii::app()->end();
	}

	public function createCacheDir($id, $model, $type)
	{
		$dirName = FPM::getBasePath() . FPM::m()->cachedImagesBaseDir . DIRECTORY_SEPARATOR . floor($id / FPM::m()->filesPerDir);

		if (!is_dir($dirName)) {
			// @TODO: fix this line. @ - is not good
			if (!@mkdir($dirName, 0777, true)) {
				throw new \CException(FPM::t('Can not create directory: ' . dirname($dirName)));
			}
		}

		$subPath = $dirName . DIRECTORY_SEPARATOR . $model . '_' . $type;

		if (!is_dir($subPath)) {
			// @TODO: fix this line. @ - is not good
			if (!@mkdir($subPath, 0777, true)) {
				throw new \CException(FPM::t('Can not create directory: ' . dirname($subPath)));
			}
		}
	}

	public function actionError()
	{
		if ($error=\Yii::app()->errorHandler->error) {
			echo $error['message'];
		}
		\Yii::app()->end();
	}
}
