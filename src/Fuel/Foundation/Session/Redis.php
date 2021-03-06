<?php
/**
 * @package    Fuel\Session
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Session;

use Fuel\Session\Driver;

/**
 * Session driver using a redis backend
 *
 * NOTE: this driver is not thread-safe.
 *
 * @package  Fuel\Session
 *
 * @since  2.0.0
 */
class Redis extends Driver
{
	/**
	 * @var  array  session driver config defaults
	 */
	protected $defaults = array(
		'cookie_name'           => 'fuelrid',
		'key_prefix'            => '',
	);

	/**
	 * @var  bool  flag to indicate session state
	 */
	protected $started = false;

	/**
	 * @var  mixed  This drivers Redis instance
	 */
	protected $redis = false;

	/**
	 * Constructor
	 *
	 * @param  array    $config  driver configuration
	 *
	 * @since  2.0.0
	 */
	public function __construct(array $config = array(), $redis)
	{
		// make sure we've got all config elements for this driver
		$config['redis'] = array_merge($this->defaults, isset($config['redis']) ? $config['redis'] : array());

		// call the parent to process the global config
		parent::__construct($config);

		// store the defined name
		if (isset($config['redis']['cookie_name']))
		{
			$this->name = $config['redis']['cookie_name'];
		}

		// store the redis storage instance
		$this->redis = $redis;
	}

    /**
     * Create a new session
     *
     * @return bool  result of the start operation
	 *
	 * @since  2.0.0
     */
    public function create()
    {
		// start the session
		if ( ! $this->started)
		{
			// generate a new session id
			$this->regenerate();

			// and start the session
			return $this->start();
		}

		return false;
	}

    /**
     * Start the session, and read existing session data back
     *
     * @return bool  result of the start operation
	 *
	 * @since  2.0.0
     */
    public function start()
    {
		// mark the session as started
		$this->started = true;

		// and read any existing session data
		return $this->read();
	}

    /**
     * Read session data
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function read()
    {
		// bail out if we don't have an active session
		if ($this->started)
		{
			// fetch the session id
			if ($sessionId = $this->findSessionId())
			{
				// and use that to fetch the payload
				$payload = $this->redis->get($this->config['redis']['key_prefix'].$this->name.'_'.$sessionId);

				// make sure we got something meaningful
				if (is_string($payload) and substr($payload,0,2) == 'a:')
				{
					// unserialize it
					$payload = unserialize($payload);

					// verify and process the payload
					return $this->processPayload($payload);
				}
			}
		}

		// no session started, or no valid session data present
		return false;
	}

    /**
     * Write session data
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function write()
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// assemble the session payload
		$payload = $this->assemblePayload();
		$expiration = $payload['security']['ex'];
		$payload = serialize($payload);

		// store it with an expiration in seconds
		if (method_exists($this->redis, 'setex'))
		{
			// Redis style
			$result = $this->redis->setex($this->config['redis']['key_prefix'].$this->name.'_'.$this->sessionId, $expiration - time(), $payload);
		}
		else
		{
			// Predis style
			$result = $this->redis->set($this->config['redis']['key_prefix'].$this->name.'_'.$this->sessionId, $payload);
			$this->redis->expire($this->config['redis']['key_prefix'].$this->name.'_'.$this->sessionId, $expiration - time());
		}

		return $result;
	}

    /**
     * Stop the session
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function stop()
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// write the data in the session
		$this->write();

		// mark the session as stopped
		$this->started = false;

		// set the session cookie
		return $this->setCookie(
			$this->name,
			$this->sessionId
		);
	}

    /**
     * Destroy the session
     *
     * @return bool  result of the write operation
	 * @since  2.0.0
     */
    public function destroy()
    {
		// we need to have a session started
		if ($this->started)
		{
			// mark the session as stopped
			$this->started = false;

			// reset the session containers
			$this->manager->reset();

			// delete the session data from the store
			$this->redis->delete($this->config['redis']['key_prefix'].$this->name.'_'.$this->sessionId);

			// delete the session cookie
			return $this->deleteCookie($this->name);
		}

		// session was not started
		return false;
	}
}
