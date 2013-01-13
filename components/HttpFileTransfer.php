<?php
namespace fileProcessor\components;
/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
abstract class HttpFileTransfer extends \CComponent implements \fileProcessor\components\IFileTransfer
{
	private $_baseDestinationDir;
	private $_maxFilesPerDir;

	/**
	 * Constructor.
	 *
	 * @param string  $baseDestinationDir base destination dir for transfer.
	 * @param integer $maxFilesPerDir     maximum files count per dir.
	 */
	public function __construct($baseDestinationDir = null, $maxFilesPerDir = null)
	{
		$this->_baseDestinationDir = $baseDestinationDir;
		$this->_maxFilesPerDir = $maxFilesPerDir;
	}

	/**
	 * Get base destination dir.
	 *
	 * @return string
	 */
	public function getBaseDestinationDir()
	{
		return $this->_baseDestinationDir;
	}

	/**
	 * Get maximum files count per dir param.
	 *
	 * @return integer
	 */
	public function getMaxFilesPerDir()
	{
		return $this->_maxFilesPerDir;
	}

	/**
	 * Save uploaded file and return full file name.
	 *
	 * @param \CUploadedFile $uploadedFile uploaded file.
	 *
	 * @throws \CException
	 * @return integer file id
	 */
	public function saveUploadedFile(\CUploadedFile $uploadedFile)
	{
		$id = $this->saveMetaDataForUploadedFile($uploadedFile);

		$dirName = $this->getBaseDestinationDir() . DIRECTORY_SEPARATOR . floor($id / $this->getMaxFilesPerDir());

		if(!is_dir($dirName))
		{
			// @TODO: fix this line. @ - is not good
			if(!@mkdir($dirName))
			{
				throw new \CException(\fileProcessor\helpers\FPM::t('Can not create directory: ' . dirname($dirName)));
			}
		}

		$fileName = $dirName . DIRECTORY_SEPARATOR . $id . '.' . mb_strtolower($uploadedFile->getExtensionName());

		$uploadedFile->saveAs($fileName);

		return $id;
	}

	/**
	 * Delete file
	 *
	 * @param integer     $id        file id
	 * @param bool|string $extension file extension
	 *
	 * @return boolean
	 */
	public function deleteFile($id, $extension = false)
	{
		if(!(int)$id)
		{
			return false;
		}

		$dirName = $this->getBaseDestinationDir() . DIRECTORY_SEPARATOR . floor($id / $this->getMaxFilesPerDir());

		if(!$extension)
		{
			$metaData = \fileProcessor\helpers\FPM::transfer()->getMetaData($id);
			$extension = $metaData['extension'];
		}
		$fileName = $dirName . DIRECTORY_SEPARATOR . $id . '.' . $extension;

		if(is_file($fileName))
		{
			$result = unlink($fileName) && $this->deleteMetaData($id) ? true : false;
		}
		else
		{
			$result = false;
		}

		return $result;
	}
}
