<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Config;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Joomla\Registry\Registry;

/**
 * Decorated Registry with bridge support for `illuminate/config`
 */
class ConfigRegistry implements Repository, \ArrayAccess
{
	/**
	 * The decorated Registry
	 *
	 * @var  Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param   Registry  $registry  The Registry to be decorated
	 */
	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
	}

	/**
	 * Magic method to proxy method calls to the decorated Registry.
	 *
	 * @param   string  $name       Method to call
	 * @param   array   $arguments  Arguments to pass to the method
	 *
	 * @return  mixed
	 */
	public function __call($name, $arguments)
	{
		if (!method_exists($this->registry, $name))
		{
			throw new \BadMethodCallException(sprintf('Call to undefined method %s::$s', static::class, $name));
		}

		return call_user_func([$this->registry, $name], ...$arguments);
	}

	/**
	 * Get all of the configuration items for the application.
	 *
	 * @return  array
	 */
	public function all()
	{
		return $this->registry->toArray();
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param   array|string  $key      Registry path (e.g. joomla.content.showauthor)
	 * @param   mixed         $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		if (is_array($key))
		{
			return $this->getMany($key);
		}

		return Arr::get($this->all(), $key, $default);
	}

	/**
	 * Get many configuration values.
	 *
	 * @param   string[]  $keys  The keys to retrieve
	 *
	 * @return  array
	 */
	public function getMany($keys)
	{
		$config = [];

		foreach ($keys as $key => $default)
		{
			if (is_numeric($key))
			{
				list($key, $default) = [$default, null];
			}

			$config[$key] = Arr::get($this->all(), $key, $default);
		}

		return $config;
	}

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param   string  $key  Registry path
	 *
	 * @return  boolean
	 */
	public function has($key)
	{
		return $this->registry->exists($key);
	}

	/**
	 * Prepend a value onto an array configuration value.
	 *
	 * @param   string  $key    Parent registry Path (e.g. joomla.content.showauthor)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  void
	 */
	public function prepend($key, $value)
	{
		$node = $this->get($key, []);

		// Convert to array if required to support prepending
		if (!is_array($node))
		{
			$node = get_object_vars($node);
		}

		array_unshift($node, $value);

		$this->set($key, $node);
	}

	/**
	 * Push a value onto an array configuration value.
	 *
	 * @param   string  $key    Parent registry Path (e.g. joomla.content.showauthor)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  void
	 */
	public function push($key, $value)
	{
		$this->registry->append($key, $value);
	}

	/**
	 * Set a given configuration value.
	 *
	 * @param   array|string  $key    Registry path (e.g. joomla.content.showauthor)
	 * @param   mixed         $value  Value of entry
	 *
	 * @return  void
	 */
	public function set($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];

		foreach ($keys as $key => $value)
		{
			$this->registry->set($key, $value);
		}
	}

	/**
	 * Determine if the given configuration option exists.
	 *
	 * @param   string  $key  Registry path
	 *
	 * @return  boolean
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}

	/**
	 * Get a configuration option.
	 *
	 * @param   string  $key  Registry path (e.g. joomla.content.showauthor)
	 *
	 * @return  mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Set a configuration option.
	 *
	 * @param   string  $key    Registry path (e.g. joomla.content.showauthor)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  void
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Unset a configuration option.
	 *
	 * @param   string  $key  Registry path (e.g. joomla.content.showauthor)
	 *
	 * @return  void
	 */
	public function offsetUnset($key)
	{
		$this->registry->remove($key);
	}
}
