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
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to add a new version of a software package
 */
final class AddSoftwareVersionCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Add Software Version');

		$software = $this->getSoftware();

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
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('add-software-version');
		$this->setDescription('Add a new version of a software package');
		$this->addArgument('version', InputArgument::REQUIRED, 'The version of software to add.');
		$this->addOption('software', null, InputOption::VALUE_OPTIONAL, 'The ID of the software package to add the version to.');
	}

	/**
	 * Get the software package either from the request data or by prompting the user.
	 *
	 * @return  Software|null
	 */
	protected function getSoftware(): ?Software
	{
		/** @var Collection|Software[] $software */
		$software = Software::all();
		$softwareId = (int) $this->getApplication()->getConsoleInput()->getOption('software');

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
