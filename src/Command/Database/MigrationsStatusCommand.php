<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command\Database;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Database migration status command
 */
final class MigrationsStatusCommand extends AbstractMigrationCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'database:migrations-status';

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Database Migrations Status');

		if (!$this->migrator->repositoryExists())
		{
			$symfonyStyle->error('No migrations found.');

			return 1;
		}

		$ran = $this->migrator->getRepository()->getRan();

		$batches    = $this->migrator->getRepository()->getMigrationBatches();
		$migrations = $this->getStatusFor($ran, $batches);

		if ($migrations->isNotEmpty())
		{
			$symfonyStyle->table(['Ran?', 'Migration', 'Batch'], $migrations->toArray());
		}
		else
		{
			$symfonyStyle->error('No migrations found');
		}

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		parent::configure();

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
