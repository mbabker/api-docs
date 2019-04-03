<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command\Database;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base database migration command
 */
abstract class AbstractMigrationCommand extends AbstractCommand
{
	/**
	 * The migrations repository
	 *
	 * @var  MigrationRepositoryInterface
	 */
	protected $migrationRepository;

	/**
	 * The database migrator
	 *
	 * @var  Migrator
	 */
	protected $migrator;

	/**
	 * Instantiate the command.
	 *
	 * @param   Migrator                      $migrator             The database migrator.
	 * @param   MigrationRepositoryInterface  $migrationRepository  The migrations repository.
	 */
	public function __construct(Migrator $migrator, MigrationRepositoryInterface $migrationRepository)
	{
		$this->migrationRepository = $migrationRepository;
		$this->migrator            = $migrator;

		parent::__construct();
	}

	/**
	 * Get the path to the database migrations.
	 *
	 * @return  string[]
	 */
	protected function getMigrationsPaths(): array
	{
		return [dirname(__DIR__, 3) . '/migrations'];
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->addOption('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.');
	}

	/**
	 * Prepare the migration database for running.
	 *
	 * @param   InputInterface  $input  The input to process.
	 *
	 * @return  void
	 */
	protected function prepareDatabase(InputInterface $input): void
	{
		$database = $input->getOption('database');

		$this->migrator->setConnection($database);
	}
}
