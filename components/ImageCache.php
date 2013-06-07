<?php
namespace fileProcessor\components;

use fileProcessor\helpers\FPM;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class ImageCache implements IImageCache
{
	/**
	 * Delete cached images.
	 *
	 * @param integer     $id image id
	 * @param bool|string $ext
	 *
	 * @return void
	 */
	public function delete($id, $ext = false)
	{
		if (!$ext) {
			$metaData = FPM::transfer()->getMetaData($id);
			$ext = $metaData['extension'];
		}

		if (!in_array($ext, array('png', 'jpeg', 'jpg', 'gif'), true)) {
			return;
		}

		$config = FPM::m()->imageSections;
		foreach ($config as $modelKey => $model) {
			foreach ($model as $typeKey => $type) {
				$fileName = FPM::getCachedImagePath($id, $modelKey, $typeKey, $ext);
				if (is_file($fileName)) {
					unlink($fileName);
				}
			}
		}
	}
}
