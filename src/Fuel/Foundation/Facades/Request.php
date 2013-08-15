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

use Fuel\Foundation\Application as AppInstance;
use Fuel\Foundation\Request\Base as RequestBase;

/**
 * Request Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Request extends Base
{
	/**
	 * Returns current active Request
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		$stack = \Dependency::resolve('requeststack');
		return $stack->top();
	}
}
