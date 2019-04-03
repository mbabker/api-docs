<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Joomla\ApiDocumentation\Model\Software;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Filter\OutputFilter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to add a new software package
 */
final class AddSoftwareCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'add-software';

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

		$symfonyStyle->title('Add Software');

		$name = $input->getOption('name');
		$slug = $input->getOption('slug');

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
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Add a new software package');
		$this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The name of the software to process');
		$this->addOption('slug', null, InputOption::VALUE_OPTIONAL, 'The slug (unique identifier) of the software');
	}
}
