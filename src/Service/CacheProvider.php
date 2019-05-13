<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Service\Exception\InvalidConfigurationException;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;

/**
 * Cache service provider
 */
final class CacheProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->alias(AdapterInterface::class, CacheItemPoolInterface::class)
			->share(CacheItemPoolInterface::class, [$this, 'getCacheService']);
	}

	/**
	 * Get the cache service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CacheItemPoolInterface
	 */
	public function getCacheService(Container $container): CacheItemPoolInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		// If caching isn't enabled then just return a void cache
		if (!$config->get('cache.enabled', false))
		{
			return new NullAdapter;
		}

		$adapter   = $config->get('cache.adapter', 'file');
		$lifetime  = $config->get('cache.lifetime', 900);
		$namespace = $config->get('cache.namespace', 'japi');

		switch ($adapter)
		{
			case 'file':
				$path = $config->get('cache.filesystem.path', 'cache');

				// If no path is given, fall back to the system's temporary directory
				if (empty($path))
				{
					$path = sys_get_temp_dir();
				}

				// If the path is relative, make it absolute... Sorry Windows users, this breaks support for your environment
				if (substr($path, 0, 1) !== '/')
				{
					$path = dirname(__DIR__, 2) . '/' . $path;
				}

				$options = [
					'file.path' => $path,
				];

				return new FilesystemAdapter($namespace, $lifetime, $path);

			case 'none':
				return new NullAdapter;

			case 'runtime':
				return new ArrayAdapter($lifetime);

			default:
				throw new InvalidConfigurationException(sprintf('The "%s" cache adapter is not supported.', $adapter));
		}
	}
}
