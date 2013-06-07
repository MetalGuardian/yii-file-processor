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
class YiiFileProcessorUrlRule extends \CBaseUrlRule
{
	protected $urlParams = array(
		'\/(\d+)\/(\w+)_(\w+)\/(\d+)\.(png|gif|jpg|jpeg)' => array(
			1 => 'sub',
			2 => 'model',
			3 => 'type',
			4 => 'id',
			5 => 'ext',
		),
	);
	/**
	 * Database connection component id
	 *
	 * @var string
	 */
	public $connectionId = 'db';

	/**
	 * Cache component id
	 *
	 * @var string
	 */
	public $cacheId = 'cache';

	/**
	 * Controller id
	 *
	 * @var string
	 */
	public $controllerId = 'image';

	/**
	 * Creates a URL based on this rule.
	 *
	 * @param \CUrlManager $manager   the manager
	 * @param string       $route     the route
	 * @param array        $params    list of parameters (name=>value) associated with the route
	 * @param string       $ampersand the token separating name-value pairs in the URL.
	 *
	 * @return mixed the constructed URL. False if this rule does not apply.
	 */
	public function createUrl($manager, $route, $params, $ampersand)
	{
		return false;
	}

	/**
	 * Parses a URL based on this rule.
	 *
	 * @param \CUrlManager  $manager     the URL manager
	 * @param \CHttpRequest $request     the request object
	 * @param string        $pathInfo    path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
	 * @param string        $rawPathInfo path info that contains the potential URL suffix
	 *
	 * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
	 */
	public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
	{
		if (!preg_match('#^' . FPM::m()->cachedImagesBaseDir . '\/#', $pathInfo)) {
			return false;
		}

		foreach ($this->urlParams as $pattern => $params) {
			if (preg_match('#^' . FPM::m()->cachedImagesBaseDir . $pattern . '#', $pathInfo, $matches)) {
				foreach ($params as $key => $paramName) {
					$_GET[$paramName] = $matches[$key];
				}

				return $this->controllerId . '/resize';
			}
		}

		return false;
	}
}
