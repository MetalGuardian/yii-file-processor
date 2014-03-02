<?php
/**
 *
 */

namespace fileProcessor\components;

use CDbCommand;
use fileProcessor\helpers\FPM;
use Yii;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileTransfer extends HttpFileTransfer
{
	/**
	 * @param null $baseDestinationDir upload directory
	 * @param null $maxFilesPerDir files per directory
	 */
	public function __construct($baseDestinationDir = null, $maxFilesPerDir = null)
	{
		if ($baseDestinationDir === null) {
			$baseDestinationDir = FPM::getBasePath() . FPM::m()->originalBaseDir;
		}

		if ($maxFilesPerDir === null) {
			$maxFilesPerDir = FPM::m()->filesPerDir;
		}

		parent::__construct($baseDestinationDir, $maxFilesPerDir);
	}

	/**
	 * Save file meta data to persistent storage and return id.
	 *
	 * @param \CUploadedFile $uploadedFile uploaded file.
	 *
	 * @return integer meta data identifier in persistent storage.
	 */
	public function saveMetaDataForUploadedFile(\CUploadedFile $uploadedFile)
	{
		$ext = \mb_strtolower($uploadedFile->getExtensionName(), 'UTF-8');
		$realName = pathinfo($uploadedFile->getName(), PATHINFO_FILENAME);

		FPM::m()->getDb()->createCommand()->insert(
			FPM::m()->tableName,
			array('extension' => $ext, 'real_name' => $realName)
		);

		return FPM::m()->getDb()->getLastInsertID();
	}

	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function saveMetaDataForFile($file)
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$realName = pathinfo($file, PATHINFO_FILENAME);
		FPM::m()->getDb()->createCommand()->insert(
			FPM::m()->tableName,
			array('extension' => $ext, 'real_name' => $realName)
		);

		return FPM::m()->getDb()->getLastInsertID();
	}

	/**
	 * Get file meta information.
	 * Example:
	 * array(
	 *        'extension' => 'jpeg',
	 * );
	 *
	 * @param integer $id file id.
	 *
	 * @param array|string $fields
	 *
	 * @return array array with file meta information.
	 */
	public function getMetaData($id, $fields = array('extension', 'real_name',))
	{
		$row = false;
		$cache = false;
		if (!empty(FPM::m()->cache) && Yii::app()->hasComponent(FPM::m()->cache)) {
			/** @var $cache \CDummyCache */
			$cache = Yii::app()->getComponent(FPM::m()->cache);
			$row = $cache->get(FPM::m()->CACHE_PREFIX . '#' . $id);
		}

		if (false === $row || !is_array($row)) {
			/** @var CDbCommand $command */
			$command = FPM::m()->getDb()->createCommand()
				->select($fields)
				->from(FPM::m()->tableName)
				->where('id = :iid', array(':iid' => $id));
			$row = $command->queryRow();
			if ($cache) {
				$cache->set(FPM::m()->CACHE_PREFIX . '#' . $id, $row, FPM::m()->cacheExpire);
			}
		}

		return $row;
	}

	/**
	 * Delete file meta information.
	 *
	 * @param integer $id file id.
	 *
	 * @return boolean
	 */
	public function deleteMetaData($id)
	{
		if (!empty(FPM::m()->cache) && Yii::app()->hasComponent(FPM::m()->cache)) {
			/** @var $cache \CDummyCache */
			$cache = Yii::app()->getComponent(FPM::m()->cache);
			$cache->delete(FPM::m()->CACHE_PREFIX . '#' . $id);
		}

		return (boolean)FPM::m()->getDb()->createCommand()->delete(
			FPM::m()->tableName,
			'id = :id',
			array(':id' => $id)
		);
	}
}
