<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Joomla\ApiDocumentation\Model\Version;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to add a new version of a software package
 */
final class AddSoftwareVersionCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'add-software-version';

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

		$symfonyStyle->title('Add Software Version');

		$software = $this->getSoftware($input, $symfonyStyle);

		if (!$software)
		{
			$symfonyStyle->error('Software not found.');

			return 1;
		}

		$version = $this->getApplication()->getConsoleInput()->getArgument('version');

		// Sanity check, if version already exists do nothing
		$versionExists = (bool) Version::query()
			->where('software', '=', $software)
			->where('version', '=', $version)
			->count();

		if ($versionExists)
		{
			$symfonyStyle->warning("Version '$version' for the '$software' software already exists.");

			return 0;
		}

		$model = new Version(['software' => $software, 'version' => $version]);
		$model->save();

		$symfonyStyle->success('Version added.');

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Add a new version of a software package');
		$this->addArgument('version', InputArgument::REQUIRED, 'The version of software to add.');
		$this->addOption('software', null, InputOption::VALUE_OPTIONAL, 'The name of the software package to add the version to.');
	}

	/**
	 * Get the software package either from the request data or by prompting the user.
	 *
	 * @param   InputInterface  $input         The input to inject into the command.
	 * @param   SymfonyStyle    $symfonyStyle  The output style object.
	 *
	 * @return  string|null
	 */
	private function getSoftware(InputInterface $input, SymfonyStyle $symfonyStyle): ?string
	{
		$software = $input->getOption('software');

		if (!$software)
		{
			$software = $symfonyStyle->choice(
				'Please select a software package to add the version to',
				[
					Version::SOFTWARE_CMS,
					Version::SOFTWARE_FRAMEWORK,
				]
			);
		}

		return $software;
	}
}
