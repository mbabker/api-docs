<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Support\ServiceProvider;
use Joomla\ApiDocumentation\Config\ConfigRegistry;
use Joomla\ApiDocumentation\Service\CacheProvider;
use Joomla\ApiDocumentation\Service\ConsoleProvider;
use Joomla\ApiDocumentation\Service\DatabaseProvider;
use Joomla\ApiDocumentation\Service\EventProvider;
use Joomla\ApiDocumentation\Service\FilesystemProvider;
use Joomla\ApiDocumentation\Service\HttpProvider;
use Joomla\ApiDocumentation\Service\LoggingProvider;
use Joomla\ApiDocumentation\Service\MigrationProvider;
use Joomla\ApiDocumentation\Service\ParserProvider;
use Joomla\ApiDocumentation\Service\RepositoryProvider;
use Joomla\ApiDocumentation\Service\TwigProvider;
use Joomla\ApiDocumentation\Service\WebApplicationProvider;
use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;

/**
 * Core Kernel
 */
abstract class Kernel implements KernelInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Flag indicating this Kernel has been booted
	 *
	 * @var  boolean
	 */
	protected $booted = false;

	/**
	 * Boot the Kernel
	 *
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function boot(): void
	{
		if ($this->booted)
		{
			return;
		}

		$this->setContainer($this->buildContainer());

		$this->booted;
	}

	/**
	 * Build the service container
	 *
	 * @return  Container
	 *
	 * @throws  \InvalidArgumentException
	 */
	protected function buildContainer(): Container
	{
		$config = $this->loadConfiguration();
		$configRepo = new ConfigRegistry($config);

		$laravelContainer = new IlluminateContainer;
		IlluminateContainer::setInstance($laravelContainer);

		$laravelContainer->instance('config', $configRepo);
		$laravelContainer->alias('config', ConfigRegistry::class);
		$laravelContainer->alias('config', Repository::class);

		$joomlaContainer = (new Container($laravelContainer))
			->share('config.decorated', $config)
			->registerServiceProvider(new CacheProvider)
			->registerServiceProvider(new ConsoleProvider)
			->registerServiceProvider(new EventProvider)
			->registerServiceProvider(new HttpProvider)
			->registerServiceProvider(new LoggingProvider)
			->registerServiceProvider(new ParserProvider)
			->registerServiceProvider(new RepositoryProvider)
			->registerServiceProvider(new TwigProvider)
			->registerServiceProvider(new WebApplicationProvider);

		/** @var ServiceProvider[] $laravelProviders */
		$laravelProviders = [
			new DatabaseProvider($laravelContainer),
			new MigrationProvider($laravelContainer),
			new FilesystemProvider($laravelContainer),
		];

		// Configure Laravel container service providers
		foreach ($laravelProviders as $provider)
		{
			$provider->register();
		}

		// Boot Laravel providers if able
		foreach ($laravelProviders as $provider)
		{
			if (method_exists($provider, 'boot'))
			{
				$laravelContainer->call([$provider, 'boot']);
			}
		}

		return $joomlaContainer;
	}

	/**
	 * Run the application
	 *
	 * @return  void
	 */
	public function run(): void
	{
		$this->boot();

		if (!$this->getContainer()->has(AbstractApplication::class))
		{
			throw new \RuntimeException('The application has not been registered with the container.');
		}

		$this->getContainer()->get(AbstractApplication::class)->execute();
	}

	/**
	 * Load the application's configuration
	 *
	 * @return  Registry
	 *
	 * @throws  \RuntimeException
	 */
	private function loadConfiguration(): Registry
	{
		$configDir = dirname(__DIR__) . '/etc';

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
