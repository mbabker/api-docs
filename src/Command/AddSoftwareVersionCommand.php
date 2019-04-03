<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Joomla\ApiDocumentation\Model\Software;
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

		$software = $this->getSoftware($input);

		if (!$software)
		{
			$symfonyStyle->error('Software not found.');

			return 1;
		}

		$version = $this->getApplication()->getConsoleInput()->getArgument('version');

		// Sanity check, if version already exists do nothing
		$versionExists = (bool) Version::query()
			->whereHas(
				'software',
				function (Builder $query) use ($software)
				{
					$query->where('id', '=', $software->id);
				}
			)
			->where('version', '=', $version)
			->count();

		if ($versionExists)
		{
			$symfonyStyle->warning("Version '$version' for the '{$software->name}' software already exists.");

			return 0;
		}

		$model = new Version(['version' => $version]);
		$model->software()->associate($software);
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
		$this->addOption('software', null, InputOption::VALUE_OPTIONAL, 'The ID of the software package to add the version to.');
	}

	/**
	 * Get the software package either from the request data or by prompting the user.
	 *
	 * @param   InputInterface  $input  The input to inject into the command.
	 *
	 * @return  Software|null
	 */
	private function getSoftware(InputInterface $input): ?Software
	{
		/** @var Collection|Software[] $software */
		$software = Software::all();
		$softwareId = (int) $input->getOption('software');

		if (!$softwareId)
		{
			$answer = $this->createSymfonyStyle()->choice(
				'Please select a software package to add the version to',
				$software->pluck('name', 'id')->toArray()
			);

			/** @var Software $chosenSoftware */
			$chosenSoftware = $software->firstWhere('name', '=', $answer);
		}
		else
		{
			/** @var Software $chosenSoftware */
			$chosenSoftware = $software->find($softwareId);
		}

		return $chosenSoftware;
	}
}
