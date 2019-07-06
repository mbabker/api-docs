<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\MigrationServiceProvider;
use Joomla\ApiDocumentation\Database\Migrations\MigrationCreator;

/**
 * Extended migration service provider
 */
final class MigrationProvider extends MigrationServiceProvider
{
	/**
	 * Register the migrator service.
	 *
	 * @return  void
	 */
	protected function registerMigrator()
	{
		/*
		 * The migrator is responsible for actually running and rollback the migration
		 * files in the application. We'll pass in our database connection resolver
		 * so the migrator can resolve any of these connections when it needs to.
		 */
		$this->app->singleton(
			'migrator',
			function ($app): Migrator
			{
				$repository = $app['migration.repository'];

				// We are not using Laravel's event system, create the migrator without the event dispatcher
				return new Migrator($repository, $app['db'], $app['files']);
			}
		);
	}

	/**
	 * Register the migration creator.
	 *
	 * @return void
	 */
	protected function registerCreator()
	{
		$this->app->singleton(
			'migration.creator',
			function ($app)
			{
				return new MigrationCreator($app['files']);
			}
		);
	}
}
