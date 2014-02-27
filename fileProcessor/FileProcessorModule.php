<?php
/**
 *
 */

namespace fileProcessor;

use CDBConnection;

/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
class FileProcessorModule extends \CWebModule
{
	/**
	 * @var string
	 */
	public $CACHE_PREFIX = 'YII.FILE.PROCESSOR.';

	/**
	 * Db component name
	 *
	 * @var string|CDBConnection
	 */
	public $db = 'db';

	/**
	 * Cache component name
	 *
	 * @var string
	 */
	public $cache = 'cache';

	/**
	 * Cache expire time
	 *
	 * @var int
	 */
	public $cacheExpire = 2592000; // 30 days

	/**
	 * @var string host for the images.
	 */
	public $host = '/';

	/**
	 * Do not change this param when created more than 1 directory - or move files to right directories
	 *
	 * @var integer max images count per dir.
	 */
	public $filesPerDir = 5000;

	/**
	 * Base path. If non default application structure
	 *
	 * @var bool|string
	 */
	public $baseDir = false;

	/**
	 * @var string original files base dir
	 */
	public $originalBaseDir = 'imageOriginal';

	/**
	 * @var string cached images base dir
	 */
	public $cachedImagesBaseDir = 'imageCached';

	/**
	 * @var array all project images definition.
	 *
	 * Example:
	 * array(
	 *        'user' => array(
	 *            'avatar' => array(
	 *                'small' => array(
	 *                    'width' => '151',
	 *                    'height' => '157',
	 *                    'type' => 'jpg',
	 *                    'quality' => 80,
	 *                ),
	 *                'medium' => array(
	 *                    'width' => '500',
	 *                    'height' => '500',
	 *                    'type' => 'jpg',
	 *                    'quality' => 80,
	 *                ),
	 *            ),
	 *        )
	 * )
	 */
	public $imageSections = array();

	/**
	 * Default page size
	 *
	 * @var int
	 */
	public $defaultPageSize = 50;

	/**
	 * @var array
	 */
	private $_imageHandler = array(
		'class' => '\fileProcessor\extensions\imageHandler\MImageHandler',
		'driver' => '\fileProcessor\extensions\imageHandler\drivers\MDriverGD',
		// \fileProcessor\extensions\imageHandler\drivers\MDriverImageMagic
	);

	/**
	 * @return array
	 */
	public function getImageHandler()
	{
		return $this->_imageHandler;
	}

	/**
	 * @param $imageHandler
	 */
	public function setImageHandler($imageHandler)
	{
		$this->_imageHandler = \CMap::mergeArray(
			$this->_imageHandler,
			$imageHandler
		);
	}
}
