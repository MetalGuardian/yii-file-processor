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
	 * @param integer $id image id
	 *
	 * @internal param bool|string $ext
	 *
	 * @return void
	 */
	public function delete($id)
	{
		$metaData = FPM::transfer()->getMetaData($id);
		if (!in_array($metaData['extension'], array('png', 'jpeg', 'jpg', 'gif'), true)) {
			return;
		}

		$config = FPM::m()->imageSections;
		foreach ($config as $modelKey => $model) {
			foreach ($model as $typeKey => $type) {
				$fileName = FPM::getCachedImagePath($id, $modelKey, $typeKey, $metaData['real_name']);
				if (is_file($fileName)) {
					unlink($fileName);
				}
			}
		}
	}
}
