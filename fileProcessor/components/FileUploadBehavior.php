<?php
/**
 *
 */

namespace fileProcessor\components;

use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileUploadBehavior extends \CActiveRecordBehavior
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
	public $scenarios = array('insert', 'update');

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
	 * @var integer the maximum file count the given attribute can hold.
	 * It defaults to 1, meaning single file upload. By defining a higher number,
	 * multiple uploads become possible.
	 */
	public $maxFiles = 1;

	/**
	 * @var string the error message used if the count of multiple uploads exceeds
	 * limit.
	 */
	public $tooMany;

	/**
	 * @param \CComponent $owner
	 *
	 * @return void
	 */
	public function attach($owner)
	{
		parent::attach($owner);
		/** @var $owner \CActiveRecord */
		//$owner = $this->getOwner();

		if (in_array($owner->getScenario(), $this->scenarios, true)) {
			// добавляем валидатор файла, не забываем в параметрах валидатора указать
			// значение safe как false
			$fileValidator = \CValidator::createValidator(
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
					'maxFiles' => $this->maxFiles,
					'tooMany' => $this->tooMany,
				)
			);
			$owner->validatorList->add($fileValidator);
		}
	}

	/**
	 * @param \CModelEvent $event
	 *
	 * @return bool|void
	 */
	public function beforeSave($event)
	{
		/** @var $owner \CActiveRecord */
		$owner = $this->getOwner();

		if (in_array($owner->getScenario(), $this->scenarios, true) && $image = \CUploadedFile::getInstance($owner, $this->attributeName)) {
			// delete old file
			$this->deleteFile();

			$image_id = FPM::transfer()->saveUploadedFile($image);

			if ($this->hasEventHandler('onSaveImage')) {
				$event = new \CEvent($this);
				$event->params = array(
					'image_id' => $image_id,
				);
				$this->onSaveImage($event);
			}

			$isset = $owner->setAttribute($this->attributeName, $image_id);
			if (!$isset && $owner->asa('multiLang')) {
				$owner->setLangAttribute($this->attributeName, $image_id);
			}
		}
	}

	public function beforeDelete($event)
	{
		$this->deleteFile();
	}

	private function deleteFile()
	{
		/** @var $owner \CActiveRecord */
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
