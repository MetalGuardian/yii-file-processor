<?php
namespace fileProcessor\components;
/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileTransfer extends \fileProcessor\components\HttpFileTransfer
{
	public function __construct($baseDestinationDir = null, $maxFilesPerDir = null)
	{
		if($baseDestinationDir === null)
			$baseDestinationDir = \Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . \fileProcessor\helpers\FPM::m()->originalBaseDir;

		if($maxFilesPerDir === null)
			$maxFilesPerDir = \fileProcessor\helpers\FPM::m()->filesPerDir;

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
		$ext = mb_strtolower($uploadedFile->getExtensionName());
		$realName = mb_strtolower($uploadedFile->getName());

		$sql = "INSERT INTO {{file}} (extension, created, real_name) VALUES (:ext, :time, :name)";
		$command = \Yii::app()->db->createCommand($sql);
		$command->execute(array(':ext'=>$ext, 'time'=>time(), ':name'=>$realName));
		return \Yii::app()->db->getLastInsertID();
	}

	/**
	 * Get file meta information.
	 * 
	 * @param integer $id file id.
	 * 
	 * @return array array with file meta information.
	 * Example:
	 * array(
	 *		'extension' => 'jpeg',
	 * );
	 */
	public function getMetaData($id)
	{
		$row = false;
		$cache = false;
		if (!empty(\fileProcessor\helpers\FPM::m()->cache) && \Yii::app()->hasComponent(\fileProcessor\helpers\FPM::m()->cache))
		{
			/** @var $cache \CDummyCache */
			$cache = \Yii::app()->getComponent(\fileProcessor\helpers\FPM::m()->cache);
			$row = $cache->get(\fileProcessor\helpers\FPM::m()->CACHE_PREFIX . '#' . $id);
		}

		if (false === $row || !is_array($row))
		{
			$sql = 'SELECT extension FROM {{file}} WHERE id = :iid';
			$command = \Yii::app()->db->createCommand($sql);
			$row = $command->queryRow(true, array(':iid'=>$id));
			if ($cache)
			{
				$cache->set(\fileProcessor\helpers\FPM::m()->CACHE_PREFIX . '#' . $id, $row, \fileProcessor\helpers\FPM::m()->cacheExpire);
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
		if (!empty(\fileProcessor\helpers\FPM::m()->cache) && \Yii::app()->hasComponent(\fileProcessor\helpers\FPM::m()->cache))
		{
			/** @var $cache \CDummyCache */
			$cache = \Yii::app()->getComponent(\fileProcessor\helpers\FPM::m()->cache);
			$cache->delete(\fileProcessor\helpers\FPM::m()->CACHE_PREFIX . '#' . $id);
		}
		$sql = 'DELETE FROM {{file}} WHERE id = :iid';
		$command = \Yii::app()->db->createCommand($sql);
		return $command->execute(array(':iid'=>$id));
	}
}