<?php
/**
 *
 */

namespace fileProcessor\extensions\imageHandler;

/**
 * Image handler
 *
 * @author  Max Lapko <maxlapko@gmail.com>
 * @see     https://github.com/maxlapko/image_processor
 * @version 0.1
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */
class MImageHandler extends \CApplicationComponent
{
	const GD_DRIVER = '\fileProcessor\extensions\imageHandler\drivers\MDriverGD';
	const IMAGE_MAGIC_DRIVER = '\fileProcessor\extensions\imageHandler\drivers\MDriverImageMagic';

	public $driver = self::GD_DRIVER;
	public $driverOptions = array();

	/**
	 *
	 * @var \fileProcessor\extensions\imageHandler\drivers\MDriverAbstract
	 */
	protected $_imageHandler;

	public function init()
	{
		parent::init();

		$this->setImageDriver($this->driver, $this->driverOptions);
	}

	/**
	 *
	 * @param string $driver
	 * @param array  $options
	 */
	public function setImageDriver($driver, $options = array())
	{
		if ($this->_imageHandler === null || \get_class($this->_imageHandler) !== $driver) {
			$this->_imageHandler = new $driver;
			foreach ($options as $key => $value) {
				$this->_imageHandler->$key = $value;
			}
			$this->driver = $driver;
		}
	}

	/**
	 * @return mixed
	 */
	public function getImageHandler()
	{
		return $this->_imageHandler;
	}

	public function __call($name, $parameters)
	{
		if (\method_exists($this->_imageHandler, $name)) {
			return \call_user_func_array(array($this->_imageHandler, $name), $parameters);
		}

		return parent::__call($name, $parameters);
	}
}
