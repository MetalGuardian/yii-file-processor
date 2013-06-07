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
					'allowEmpty' => $this->allowEmpty,
					'safe' => false,
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

			$owner->setAttribute($this->attributeName, $image_id);
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
}
