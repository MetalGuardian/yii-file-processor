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
class FileTransfer extends HttpFileTransfer
{
	public function __construct($baseDestinationDir = null, $maxFilesPerDir = null)
	{
		if ($baseDestinationDir === null) {
			$baseDestinationDir = \Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . FPM::m()->originalBaseDir;
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
		$ext = \mb_strtolower($uploadedFile->getExtensionName());
		$realName = $uploadedFile->getName();

		$sql = "INSERT INTO {{file}} (extension, created, real_name) VALUES (:ext, :time, :name)";
		$command = \Yii::app()->db->createCommand($sql);
		$command->execute(array(':ext' => $ext, 'time' => time(), ':name' => $realName));

		return \Yii::app()->db->getLastInsertID();
	}

	public function saveMetaDataForFile($realName, $ext)
	{
		$sql = "INSERT INTO {{file}} (extension, created, real_name) VALUES (:ext, :time, :name)";
		$command = \Yii::app()->db->createCommand($sql);
		$command->execute(array(':ext'=>$ext, 'time'=>time(), ':name'=>$realName));

		return \Yii::app()->db->getLastInsertID();
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
	 * @param string  $fields
	 *
	 * @return array array with file meta information.
	 */
	public function getMetaData($id, $fields = 'extension')
	{
		$row = false;
		$cache = false;
		if (!empty(FPM::m()->cache) && \Yii::app()->hasComponent(FPM::m()->cache)) {
			/** @var $cache \CDummyCache */
			$cache = \Yii::app()->getComponent(FPM::m()->cache);
			$row = $cache->get(FPM::m()->CACHE_PREFIX . '#' . $id);
		}

		if (false === $row || !is_array($row)) {
			$command = \Yii::app()->db->createCommand()
				->select($fields)
				->from('{{file}}')
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
		if (!empty(FPM::m()->cache) && \Yii::app()->hasComponent(FPM::m()->cache)) {
			/** @var $cache \CDummyCache */
			$cache = \Yii::app()->getComponent(FPM::m()->cache);
			$cache->delete(FPM::m()->CACHE_PREFIX . '#' . $id);
		}
		$sql = 'DELETE FROM {{file}} WHERE id = :iid';
		$command = \Yii::app()->db->createCommand($sql);

		return $command->execute(array(':iid' => $id));
	}
}
