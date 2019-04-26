<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Command\AddSoftwareCommand;
use Joomla\ApiDocumentation\Command\AddSoftwareVersionCommand;
use Joomla\ApiDocumentation\Command\Database\MakeMigrationCommand;
use Joomla\ApiDocumentation\Command\Database\MigrateCommand;
use Joomla\ApiDocumentation\Command\Database\MigrationsStatusCommand;
use Joomla\ApiDocumentation\Command\ImportDataCommand;
use Joomla\ApiDocumentation\Command\ParseFilesCommand;
use Joomla\ApiDocumentation\Importer\ParsedDataImporter;
use Joomla\ApiDocumentation\Parser\FilesystemParser;
use Joomla\Console\Application;
use Joomla\Console\Loader\ContainerLoader;
use Joomla\Console\Loader\LoaderInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Command\DebugEventDispatcherCommand;
use Joomla\Event\DispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Console service provider
 */
final class ConsoleProvider implements ServiceProviderInterface
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
		$container->share(Application::class, [$this, 'getConsoleApplicationClassService']);

		$container->alias(ContainerLoader::class, LoaderInterface::class)
			->share(LoaderInterface::class, [$this, 'getApplicationConsoleLoaderService']);

		/*
		 * App commands
		 */

		$this->registerDatabaseCommands($container);

		$container->share(AddSoftwareCommand::class, [$this, 'getAddSoftwareCommandClassService']);
		$container->share(AddSoftwareVersionCommand::class, [$this, 'getAddSoftwareVersionCommandClassService']);
		$container->share(ImportDataCommand::class, [$this, 'getImportDataCommandClassService']);
		$container->share(ParseFilesCommand::class, [$this, 'getParseFilesCommandClassService']);

		/*
		 * Package commands
		 */

		$container->share(DebugEventDispatcherCommand::class, [$this, 'getDebugEventDispatcherCommandClassService']);
	}

	/**
	 * Registers the database commands to the container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	private function registerDatabaseCommands(Container $container)
	{
		$container->share(MakeMigrationCommand::class, [$this, 'getDatabaseMakeMigrationCommandClassService']);
		$container->share(MigrateCommand::class, [$this, 'getDatabaseMigrateCommandClassService']);
		$container->share(MigrationsStatusCommand::class, [$this, 'getDatabaseMigrationsStatusCommandClassService']);
	}

	/**
	 * Get the add software command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AddSoftwareCommand
	 */
	public function getAddSoftwareCommandClassService(Container $container): AddSoftwareCommand
	{
		return new AddSoftwareCommand;
	}

	/**
	 * Get the add software version command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AddSoftwareVersionCommand
	 */
	public function getAddSoftwareVersionCommandClassService(Container $container): AddSoftwareVersionCommand
	{
		return new AddSoftwareVersionCommand;
	}

	/**
	 * Get the command loader class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoaderInterface
	 */
	public function getApplicationConsoleLoaderService(Container $container): LoaderInterface
	{
		$mapping = [
			AddSoftwareCommand::getDefaultName()          => AddSoftwareCommand::class,
			AddSoftwareVersionCommand::getDefaultName()   => AddSoftwareVersionCommand::class,
			MakeMigrationCommand::getDefaultName()        => MakeMigrationCommand::class,
			MigrateCommand::getDefaultName()              => MigrateCommand::class,
			MigrationsStatusCommand::getDefaultName()     => MigrationsStatusCommand::class,
			ImportDataCommand::getDefaultName()           => ImportDataCommand::class,
			ParseFilesCommand::getDefaultName()           => ParseFilesCommand::class,
			DebugEventDispatcherCommand::getDefaultName() => DebugEventDispatcherCommand::class,
		];

		return new ContainerLoader($container, $mapping);
	}

	/**
	 * Get the console application service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Application
	 */
	public function getConsoleApplicationClassService(Container $container): Application
	{
		$application = new Application(new ArgvInput, new ConsoleOutput, $container->get('config.decorated'));

		$application->setName('Joomla! API Documentation');
		$application->setCommandLoader($container->get(LoaderInterface::class));
		$application->setDispatcher($container->get(DispatcherInterface::class));
		$application->setLogger($container->get(LoggerInterface::class));

		return $application;
	}

	/**
	 * Get the database make migration command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MakeMigrationCommand
	 */
	public function getDatabaseMakeMigrationCommandClassService(Container $container): MakeMigrationCommand
	{
		return new MakeMigrationCommand(
			$container->get('migration.creator'),
			$container->get('migrator'),
			$container->get('migration.repository')
		);
	}

	/**
	 * Get the database migrate command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MigrateCommand
	 */
	public function getDatabaseMigrateCommandClassService(Container $container): MigrateCommand
	{
		return new MigrateCommand(
			$container->get('migrator'),
			$container->get('migration.repository')
		);
	}

	/**
	 * Get the database migration status command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MigrationsStatusCommand
	 */
	public function getDatabaseMigrationsStatusCommandClassService(Container $container): MigrationsStatusCommand
	{
		return new MigrationsStatusCommand(
			$container->get('migrator'),
			$container->get('migration.repository')
		);
	}

	/**
	 * Get the debug event dispatcher command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugEventDispatcherCommand
	 */
	public function getDebugEventDispatcherCommandClassService(Container $container): DebugEventDispatcherCommand
	{
		return new DebugEventDispatcherCommand(
			$container->get(DispatcherInterface::class)
		);
	}

	/**
	 * Get the import data command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ImportDataCommand
	 */
	public function getImportDataCommandClassService(Container $container): ImportDataCommand
	{
		return new ImportDataCommand(
			$container->get(ParsedDataImporter::class)
		);
	}

	/**
	 * Get the parse files command class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ParseFilesCommand
	 */
	public function getParseFilesCommandClassService(Container $container): ParseFilesCommand
	{
		return new ParseFilesCommand(
			$container->get(FilesystemParser::class)
		);
	}
}
