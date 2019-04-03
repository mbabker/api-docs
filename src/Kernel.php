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
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Database\Migrations\MigrationCreator as IlluminateMigrationCreator;
use Illuminate\Database\Schema\Builder;
use Illuminate\Filesystem\Filesystem;
use Joomla\ApiDocumentation\Config\ConfigRegistry;
use Joomla\ApiDocumentation\Database\Migrations\MigrationCreator;
use Joomla\ApiDocumentation\Service\ConsoleProvider;
use Joomla\ApiDocumentation\Service\EventProvider;
use Joomla\ApiDocumentation\Service\LoggingProvider;
use Joomla\ApiDocumentation\Service\ParserProvider;
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
			->registerServiceProvider(new ConsoleProvider)
			->registerServiceProvider(new EventProvider)
			->registerServiceProvider(new LoggingProvider)
			->registerServiceProvider(new ParserProvider);

		$joomlaContainer->share('config.decorated', $config);

		// Configure Laravel container service providers
		(new DatabaseServiceProvider($laravelContainer))->register();
		(new MigrationServiceProvider($laravelContainer))->register();

		// We're using an extended migration creator, change the service in the Laravel container
		$laravelContainer->extend(
			'migration.creator',
			function (IlluminateMigrationCreator $original, IlluminateContainer $app)
			{
				return new MigrationCreator($app->make('files'));
			}
		);

		// We're only using the Filesystem class from Laravel, the provider also configures its filesystem manager and disks so manually do this
		$laravelContainer->singleton(
			'files',
			function ()
			{
				return new Filesystem;
			}
		);
		$laravelContainer->alias('files', Filesystem::class);

		// Set up the database's capsule
		$manager = new Manager($laravelContainer);
		$manager->setAsGlobal();

		// We don't have Laravel's event system wired in, so do what the DatabaseServiceProvider's boot method does without it
		Model::setConnectionResolver($laravelContainer->make('db'));

		// Set the default key length to account for utf8mb4 and MySQL 5.7 quirks
		Builder::defaultStringLength(191);

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
