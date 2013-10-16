<?php
/**
 *
 */

namespace fileProcessor\components;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
interface IFileTransfer
{
	/**
	 * Constructor.
	 *
	 * @param string  $baseDestinationDir base destination dir for transfer.
	 * @param integer $maxFilesPerDir     maximum files count per dir.
	 */
	public function __construct($baseDestinationDir = null, $maxFilesPerDir = null);

	/**
	 * Get base destination dir.
	 *
	 * @return string
	 */
	public function getBaseDestinationDir();

	/**
	 * Get maximum files count per dir param.
	 *
	 * @return integer
	 */
	public function getMaxFilesPerDir();

	/**
	 * Save uploaded file and return full file name.
	 *
	 * @param \CUploadedFile $uploadedFile
	 *
	 * @throws \CException
	 * @return integer file id
	 */
	public function saveUploadedFile(\CUploadedFile $uploadedFile);

	public function saveFile($file, $ext);

	/**
	 * Delete file
	 *
	 * @param integer     $id        file id
	 * @param bool|string $extension file extension.
	 *
	 * @return boolean
	 */
	public function deleteFile($id, $extension = false);

	/**
	 * Save file meta data to persistent storage and return id.
	 *
	 * @param \CUploadedFile $uploadedFile uploaded file.
	 *
	 * @return integer meta data identifier in persistent storage.
	 */
	public function saveMetaDataForUploadedFile(\CUploadedFile $uploadedFile);

	public function saveMetaDataForFile($realName, $ext);

	/**
	 * Get file meta information.
	 *
	 * @param integer $id file id.
	 *
	 * @return array array with file meta information.
	 * Example:
	 * array(
	 *        'extension' => 'jpeg',
	 *        'is_ready' => 1,
	 *        ...
	 *        'some_key' => 'some_value'
	 * );
	 */
	public function getMetaData($id);

	/**
	 * Delete file meta information.
	 *
	 * @param integer $id file id.
	 *
	 * @return boolean
	 */
	public function deleteMetaData($id);
}
