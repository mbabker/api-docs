<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Command;

use Joomla\ApiDocumentation\Importer\ParsedDataImporter;
use Joomla\ApiDocumentation\Model\Version;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to parse the data export for a release
 */
final class ImportDataCommand extends AbstractCommand
{
	/**
	 * Internal tracker of stable releases for select software.
	 *
	 * @const  array
	 * @todo   Make this more dynamic
	 */
	private const STABLE_RELEASES = [
		Version::SOFTWARE_CMS => [
			'2.5' => '2.5.28',
			'3.x' => '3.9.6',
		]
	];

	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'import-data';

	/**
	 * Path to the data file.
	 *
	 * @var  string
	 */
	private $dataFile;

	/**
	 * The data importer.
	 *
	 * @var  ParsedDataImporter
	 */
	private $importer;

	/**
	 * Constructor.
	 *
	 * @param   ParsedDataImporter  $importer  The data importer.
	 */
	public function __construct(ParsedDataImporter $importer)
	{
		parent::__construct();

		$this->dataFile = dirname(__DIR__, 2) . '/data.json';
		$this->importer = $importer;
	}

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

		$symfonyStyle->title('Parse Data Export');

		if (!file_exists($this->dataFile))
		{
			$symfonyStyle->error('Data export missing, have you run the `parse-files` command?');

			return 1;
		}

		$software = $input->getArgument('software');
		$version  = $input->getArgument('version');

		switch ($software)
		{
			case Version::SOFTWARE_CMS:
				if (!isset(self::STABLE_RELEASES[Version::SOFTWARE_CMS][$version]))
				{
					$symfonyStyle->error("Unknown CMS version '$version'");

					return 1;
				}

				$softwareVersion = self::STABLE_RELEASES[Version::SOFTWARE_CMS][$version];

				break;

			case Version::SOFTWARE_FRAMEWORK:
				$symfonyStyle->warning('The Framework is not supported at this time.');

				return 1;

			default:
				$symfonyStyle->error("Unknown software package '$software'");

				return 1;
		}

		$versionModel = Version::query()
			->where('version', '=', $version)
			->firstOrFail();

		$importData = json_decode(file_get_contents($this->dataFile), true);

		$this->importer->importData($importData, $versionModel);

		$symfonyStyle->success('Data imported');

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Import the data dump for a given release');
		$this->addArgument('software', InputArgument::REQUIRED, 'The software package to process');
		$this->addArgument('version', InputArgument::REQUIRED, 'The software version to process');
	}
}
