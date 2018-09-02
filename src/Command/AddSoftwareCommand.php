<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Joomla\ApiDocumentation\Model\Software;
use Joomla\Console\AbstractCommand;
use Joomla\Filter\OutputFilter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

/**
 * Command to add a new software package
 */
final class AddSoftwareCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Add Software');

		$name = $this->getApplication()->getConsoleInput()->getOption('name');
		$slug = $this->getApplication()->getConsoleInput()->getOption('slug');

		if (!$name)
		{
			$question = new Question('What is the name of the software package to add?');
			$question->setMaxAttempts(3);
			$question->setValidator(
				function (?string $response)
				{
					if (!\is_string($response) || $response === '')
					{
						throw new \RuntimeException('Please enter a name for the software package.');
					}

					return $response;
				}
			);

			$name = $symfonyStyle->askQuestion($question);
		}

		if (!$slug)
		{
			$slug = $symfonyStyle->ask('What is the slug (unique identifier) for this softare package? [Leave blank to auto-generate]');
		}

		// Sanity check, if name already exists do nothing
		$nameExists = (bool) Software::query()
			->where('name', '=', $name)
			->count();

		if ($nameExists)
		{
			$symfonyStyle->warning("The '$name' software already exists.");

			return 0;
		}

		// Generate the slug if need be
		if (!$slug)
		{
			$slug = OutputFilter::stringUrlUnicodeSlug($name);
		}

		Software::query()->create(
			[
				'name' => $name,
				'slug' => $slug,
			]
		);

		$symfonyStyle->success('Software added.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('add-software');
		$this->setDescription('Add a new software package');
		$this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The name of the software to process');
		$this->addOption('slug', null, InputOption::VALUE_OPTIONAL, 'The slug (unique identifier) of the software');
	}
}
