<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command\Database;

use Illuminate\Support\Collection;

/**
 * Database migration status command
 */
final class MigrationsStatusCommand extends AbstractMigrationCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$input        = $this->getApplication()->getConsoleInput();
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Database Migrations Status');

		$this->prepareDatabase();

		if (!$this->migrator->repositoryExists())
		{
			$symfonyStyle->error('No migrations found.');

			return 1;
		}

		$ran = $this->migrator->getRepository()->getRan();

		$batches = $this->migrator->getRepository()->getMigrationBatches();

		if (count($migrations = $this->getStatusFor($ran, $batches)) > 0)
		{
			$symfonyStyle->table(['Ran?', 'Migration', 'Batch'], $migrations);
		}
		else
		{
			$symfonyStyle->error('No migrations found');
		}

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		parent::initialise();

		$this->setName('database:migrations-status');
		$this->setDescription('Check the status of the database migrations');
	}

	/**
	 * Get the migration files.
	 *
	 * @return  array
	 */
	private function getAllMigrationFiles(): array
	{
		return $this->migrator->getMigrationFiles($this->getMigrationsPaths());
	}

	/**
	 * Get the status for the given ran migrations.
	 *
	 * @param   array  $ran      The completed migrations
	 * @param   array  $batches  The migration batches
	 *
	 * @return  Collection
	 */
	private function getStatusFor(array $ran, array $batches): Collection
	{
		return Collection::make($this->getAllMigrationFiles())
			->map(
				function ($migration) use ($ran, $batches)
				{
					$migrationName = $this->migrator->getMigrationName($migration);

					return in_array($migrationName, $ran)
						? ['<info>Y</info>', $migrationName, $batches[$migrationName]]
						: ['<fg=red>N</fg=red>', $migrationName];
				}
			);
	}
}
