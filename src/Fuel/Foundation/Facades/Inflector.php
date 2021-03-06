<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

/**
 * Inflector Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Inflector extends Base
{
	/**
	 * Returns an instance of the Inflector object.
	 *
	 * @return  Fuel\Common\Inflector
	 *
	 * @since  2.0.0
	 */
	public static function forge()
	{
		return static::$dic->resolve('inflector', func_get_args());
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Common\Inflector
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}

