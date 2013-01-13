<?php
namespace fileProcessor\components;
/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
interface IImageCache
{
	/**
	 * Delete cached images
	 * 
	 * @param integer $id image id
	 */
	public function delete($id);
}