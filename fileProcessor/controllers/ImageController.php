<?php
/**
 *
 */

namespace fileProcessor\controllers;

use CController;
use CHttpException;
use fileProcessor\helpers\FPM;
use Yii;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class ImageController extends CController
{
	public $layout = false;

	public function init()
	{
		parent::init();
		Yii::app()->errorHandler->errorAction = $this->route . '/error';
	}

	/**
	 * @param $model
	 * @param $type
	 * @param $id
	 * @param $fileName
	 * @param $ext
	 *
	 * @throws \CHttpException
	 */
	public function actionResize($model, $type, $id, $fileName, $ext)
	{
		$file = FPM::getOriginalFilePath($id, $fileName, $ext);
		if (file_exists($file)) {
			$meta = FPM::transfer()->getMetaData($id);
			if (!(is_array($meta) && $fileName === $meta['real_name'])) {
				throw new CHttpException(404, 'File not found');
			}
			$config = isset(FPM::m()->imageSections[$model]) && isset(FPM::m()->imageSections[$model][$type]) ? FPM::m()->imageSections[$model][$type] : null;
			if (!$config) {
				throw new CHttpException(400, 'Incorrect request');
			}
			$thumbFile = FPM::getCachedImagePath($id, $model, $type, $id . '-' . $meta['real_name'] . '.' . $meta['extension']);

			FPM::createCacheDir($id, $model, $type);

			/** @var $ih \fileProcessor\extensions\imageHandler\drivers\MDriverAbstract|\fileProcessor\extensions\imageHandler\MImageHandler */
			$ih = Yii::createComponent(FPM::m()->imageHandler);
			$ih->init();
			$ih->load($file);
			if (isset($config['do'])) {
				switch($config['do'])
				{
					case 'adaptiveResize':
						$ih->adaptiveThumb($config['width'], $config['height']);
						break;
					case 'resize':
						$ih->resize($config['width'], $config['height']);
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
			throw new CHttpException(404, 'File not found');
		}
		Yii::app()->end();
	}

	public function actionError()
	{
		if (($error= Yii::app()->errorHandler->error)) {
			echo $error['message'];
		}
		Yii::app()->end();
	}
}
