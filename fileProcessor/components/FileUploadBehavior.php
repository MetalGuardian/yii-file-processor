<?php
/**
 *
 */

namespace fileProcessor\components;

use CActiveRecord;
use CActiveRecordBehavior;
use CEvent;
use CModelEvent;
use CUploadedFile;
use CValidator;
use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileUploadBehavior extends CActiveRecordBehavior
{
	/**
	 * File attribute name
	 *
	 * @var string
	 */
	public $attributeName = 'file_id';

	/**
	 * Can attribute be empty
	 *
	 * @var bool
	 */
	public $allowEmpty = true;

	/**
	 * ActiveRecord scenarios
	 *
	 * @var array
	 */
	public $scenarios = array('insert', 'update', );

	/**
	 * Allowed file types
	 *
	 * @var string | array
	 */
	public $fileTypes = 'png, gif, jpeg, jpg';

	/**
	 * @var mixed a list of MIME-types of the file that are allowed to be uploaded.
	 * This can be either an array or a string consisting of MIME-types separated
	 * by space or comma (e.g. "image/gif, image/jpeg"). MIME-types are
	 * case-insensitive. Defaults to null, meaning all MIME-types are allowed.
	 * In order to use this property fileinfo PECL extension should be installed.
	 * @since 1.1.11
	 */
	public $mimeTypes;

	/**
	 * @var integer the minimum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * @see tooSmall
	 */
	public $minSize;

	/**
	 * @var integer the maximum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * Note, the size limit is also affected by 'upload_max_filesize' INI setting
	 * and the 'MAX_FILE_SIZE' hidden field value.
	 * @see tooLarge
	 */
	public $maxSize;

	/**
	 * @var string the error message used when the uploaded file is too large.
	 * @see maxSize
	 */
	public $tooLarge;

	/**
	 * @var string the error message used when the uploaded file is too small.
	 * @see minSize
	 */
	public $tooSmall;

	/**
	 * @var string the error message used when the uploaded file has an extension name
	 * that is not listed among {@link types}.
	 */
	public $wrongType;

	/**
	 * @var string the error message used when the uploaded file has a MIME-type
	 * that is not listed among {@link mimeTypes}. In order to use this property
	 * fileinfo PECL extension should be installed.
	 * @since 1.1.11
	 */
	public $wrongMimeType;

	/**
	 * Responds to {@link CModel::onBeforeValidate} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the validation process.
	 * @param CModelEvent $event event parameter
	 */
	public function beforeValidate($event)
	{
		/** @var CActiveRecord $owner */
		$owner = $this->getOwner();
		if (in_array($owner->getScenario(), $this->scenarios, true)) {
			// добавляем валидатор файла, не забываем в параметрах валидатора указать
			// значение safe как false
			$fileValidator = CValidator::createValidator(
				'file',
				$owner,
				$this->attributeName,
				array(
					'types' => $this->fileTypes,
					'allowEmpty' => $this->allowEmpty || $owner->{$this->attributeName},
					'safe' => false,
					'mimeTypes' => $this->mimeTypes,
					'minSize' => $this->minSize,
					'maxSize' => $this->maxSize,
					'tooLarge' => $this->tooLarge,
					'tooSmall' => $this->tooSmall,
					'wrongType' => $this->wrongType,
					'wrongMimeType' => $this->wrongMimeType,
				)
			);
			$owner->validatorList->add($fileValidator);
		}
	}


	/**
	 * @param CModelEvent $event
	 *
	 * @return bool|void
	 */
	public function beforeSave($event)
	{
		/** @var $owner CActiveRecord */
		$owner = $this->getOwner();
		if (in_array($owner->getScenario(), $this->scenarios, true) && $image = CUploadedFile::getInstance(
				$owner,
				$this->attributeName
			)
		) {
			// delete old file
			$this->deleteFile();

			$image_id = FPM::transfer()->saveUploadedFile($image);

			if ($this->hasEventHandler('onSaveImage')) {
				$event = new CEvent($this);
				$event->params = array(
					'image_id' => $image_id,
				);
				$this->onSaveImage($event);
			}

			$owner->{$this->attributeName} = $image_id;
		}
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeDelete} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link CBehavior::owner owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the deletion process.
	 * @param CEvent $event event parameter
	 */
	public function beforeDelete($event)
	{
		$this->deleteFile();
	}

	private function deleteFile()
	{
		/** @var $owner CActiveRecord */
		$owner = $this->getOwner();

		if ($owner->getAttribute($this->attributeName)) {
			$metaData = FPM::transfer()->getMetaData($owner->getAttribute($this->attributeName));
			FPM::deleteFiles($owner->getAttribute($this->attributeName), $metaData['extension']);
		}
	}

	/**
	 * OnSaveImage event
	 *
	 * @param $event
	 */
	public function onSaveImage($event)
	{
		$this->raiseEvent('onSaveImage', $event);
	}
}
