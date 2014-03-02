<?php
/**
 *
 */

namespace fileProcessor\components;

use CActiveRecordBehavior;
use CEvent;
use CModelEvent;
use core\components\ActiveRecord;
use CUploadedFile;
use CValidator;
use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileMultiUploadBehavior extends CActiveRecordBehavior
{

	public $fileUploader;

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
	public $scenarios = array('insert', 'update',);

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
	 * Responds to {@link CModel::onBeforeValidate} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the validation process.
	 *
	 * @param CModelEvent $event event parameter
	 */
	public function beforeValidate($event)
	{
		/** @var ActiveRecord $owner */
		$owner = $this->getOwner();
		if (in_array($owner->getScenario(), $this->scenarios, true)) {
			// добавляем валидатор файла, не забываем в параметрах валидатора указать
			// значение safe как false
			$fileValidator = CValidator::createValidator(
				'file',
				$owner,
				'fileUploader',
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
	 * @param CModelEvent $event
	 *
	 * @return bool|void
	 */
	public function beforeSave($event)
	{
		/** @var $owner ActiveRecord */
		$owner = $this->getOwner();
		if (!$owner->isNewRecord && in_array(
				$owner->getScenario(),
				$this->scenarios,
				true
			) && ($files = CUploadedFile::getInstances(
				$owner,
				'fileUploader'
			)) && !empty($files)
		) {
			/** @var CUploadedFile[] $files */
			foreach ($files as $file) {
				$fileId = FPM::transfer()->saveUploadedFile($file);

				if ($this->hasEventHandler('onSaveImage')) {
					$event = new CEvent($this);
					$event->params = array(
						'fileId' => $fileId,
					);
					$this->onSaveImage($event);
				}

				FPM::m()->getDb()->createCommand()->insert(
					FPM::m()->relatedTableName,
					array(
						'file_id' => $fileId,
						'model_id' => $owner->getPrimaryKey(),
						'model_class' => $owner->getClassName(),
					)
				);
			}
		}
	}

	/**
	 * Responds to {@link ActiveRecord::onBeforeDelete} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link CBehavior::owner owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the deletion process.
	 *
	 * @param CEvent $event event parameter
	 */
	public function beforeDelete($event)
	{
		$this->deleteFiles();
	}

	private function deleteFiles()
	{
		/** @var $owner ActiveRecord */
		$owner = $this->getOwner();

		$files = FPM::m()->getDb()->createCommand()->select(array('file_id'))->from(FPM::m()->relatedTableName)->where(
			'model_id = :mid AND model_class = :mclass'
		)->queryColumn(array(':mid' => $owner->getPrimaryKey(), ':mclass' => $owner->getClassName(),));
		foreach ($files as $fileId) {
			FPM::deleteFiles($fileId);
		}
	}

	/**
	 * Return array of file ids
	 *
	 * @return array
	 */
	public function getRelatedFiles()
	{
		/** @var ActiveRecord $owner */
		$owner = $this->getOwner();
		$files = FPM::m()->getDb()->createCommand()->select(array('file_id'))->from(FPM::m()->relatedTableName)->where(
			'model_id = :mid AND model_class = :mclass'
		)->queryColumn(array(':mid' => $owner->getPrimaryKey(), ':mclass' => $owner->getClassName(),));

		return $files;
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
