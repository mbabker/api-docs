<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command\Database;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Database migration command
 */
final class MigrateCommand extends AbstractMigrationCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'database:migrate';

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

		$symfonyStyle->title('Migrate Database');

		$this->ensureMigrationRepositoryExists($symfonyStyle);

		$this->migrator->setOutput(new OutputStyle($input, $output));

		if ($input->getOption('refresh'))
		{
			$this->migrator->reset(
				$this->getMigrationsPaths(),
				$input->getOption('pretend')
			);
		}

		$this->migrator->run(
			$this->getMigrationsPaths(),
			[
				'pretend' => $input->getOption('pretend'),
				'step'    => $input->getOption('step'),
			]
		);

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

		$this->setDescription('Migrate the database to the current version');
		$this->addOption('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.');
		$this->addOption('refresh', null, InputOption::VALUE_NONE, 'Resets the database and re-runs all migrations');
		$this->addOption('step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted & re-run.');
	}

	/**
	 * Prepare the migration database for running.
	 *
	 * @param   SymfonyStyle  $symfonyStyle  The output object.
	 *
	 * @return  void
	 */
	protected function ensureMigrationRepositoryExists(SymfonyStyle $symfonyStyle): void
	{
		if (!$this->migrator->repositoryExists())
		{
			$this->migrationRepository->setSource($this->migrator->getConnection());

			$this->migrationRepository->createRepository();

			$symfonyStyle->success('Migration table created successfully.');
		}
	}
}
