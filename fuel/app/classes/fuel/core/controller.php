<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

class Controller extends \Fuel\Core\Controller
{
	/**
	 * @var  Array  The variable to use in view
	 */
	private $varArray = [];

	public function __construct($request)
	{
		parent::__construct($request);
	}

	/**
	 * This method set variables to use in view
	 * @param String $key, String $value
	 * @return void
	 */
	protected function setVar($key, $value)
	{
		$this->varArray[$key] = $value;
	}

	/**
	 * This method get variables setted variables to use in view
	 * @return Array
	 */
	public function getVar()
	{
		return $this->varArray;
	}
}
