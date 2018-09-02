<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Configuration service provider
 */
final class ConfigurationProvider implements ServiceProviderInterface
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
		$container->share('config', [$this, 'getConfigService'], true);
	}

	/**
	 * Get the config service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Registry
	 */
	public function getConfigService(Container $container) : Registry
	{
		$configDir = dirname(__DIR__, 2) . '/etc';

		$registry = new Registry;

		if (!file_exists($configDir . '/config.yaml'))
		{
			throw new \RuntimeException('The configuration file is missing.');
		}

		$registry->loadFile($configDir . '/config.yaml', 'YAML');
		$registry->loadFile($configDir . '/versions.yaml', 'YAML');

		return $registry;
	}
}
