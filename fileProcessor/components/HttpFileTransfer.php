<?php
/**
 *
 */

namespace fileProcessor\components;

use CComponent;
use fileProcessor\components\IFileTransfer;
use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
abstract class HttpFileTransfer extends CComponent implements IFileTransfer
{
	/**
	 * @var null|string
	 */
	private $_baseDestinationDir;

	/**
	 * @var int|null
	 */
	private $_maxFilesPerDir;

	/**
	 * Constructor.
	 *
	 * @param string $baseDestinationDir base destination dir for transfer.
	 * @param integer $maxFilesPerDir maximum files count per dir.
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

		if (!is_dir($dirName)) {
			// @TODO: fix this line. @ - is not good
			if (!@mkdir($dirName, 0777, true)) {
				throw new \CException('Can not create directory: ' . dirname($dirName));
			}
		}

		$fileName = $dirName . DIRECTORY_SEPARATOR . $id . '.' . \mb_strtolower($uploadedFile->getExtensionName());

		$uploadedFile->saveAs($fileName);

		return $id;
	}

	/**
	 * @param $file
	 * @param $ext
	 *
	 * @return mixed
	 * @throws \CException
	 */
	public function saveFile($file, $ext)
	{
		$id = $this->saveMetaDataForFile('', $ext);

		$dirName = $this->getBaseDestinationDir() . DIRECTORY_SEPARATOR . floor($id / $this->getMaxFilesPerDir());

		if (!is_dir($dirName)) {
			// @TODO: fix this line. @ - is not good
			if (!@mkdir($dirName, 0777, true)) {
				throw new \CException('Can not create directory: ' . dirname($dirName));
			}
		}

		$fileName = $dirName . DIRECTORY_SEPARATOR . $id . '.' . \mb_strtolower($ext);

		$this->putImage($ext, $file, $fileName);

		return $id;
	}

	/**
	 * @param $ext
	 * @param $img
	 * @param null $file
	 */
	public function putImage($ext, $img, $file = null)
	{
		switch ($ext) {
			case "png":
				imagepng($img, ($file != null ? $file : ''));
				break;
			case "jpeg":
				imagejpeg($img, ($file ? $file : ''), 90);
				break;
			case "jpg":
				imagejpeg($img, ($file ? $file : ''), 90);
				break;
			case "gif":
				imagegif($img, ($file ? $file : ''));
				break;
		}
	}

	/**
	 * Delete file
	 *
	 * @param integer $id file id
	 * @param bool|string $extension file extension
	 *
	 * @return boolean
	 */
	public function deleteFile($id, $extension = false)
	{
		if (!(int)$id) {
			return false;
		}

		$dirName = $this->getBaseDestinationDir() . DIRECTORY_SEPARATOR . floor($id / $this->getMaxFilesPerDir());

		if (!$extension) {
			$metaData = FPM::transfer()->getMetaData($id);
			$extension = $metaData['extension'];
		}
		$fileName = $dirName . DIRECTORY_SEPARATOR . $id . '.' . $extension;

		if (is_file($fileName)) {
			$result = unlink($fileName) && $this->deleteMetaData($id) ? true : false;
		} else {
			$result = false;
		}

		return $result;
	}
}
