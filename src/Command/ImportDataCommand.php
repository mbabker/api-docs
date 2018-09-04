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
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

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
		'cms' => [
			'2.5' => '2.5.28',
			'3.x' => '3.8.12',
		]
	];

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
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Parse Data Export');

		if (!file_exists($this->dataFile))
		{
			$symfonyStyle->error('Data export missing, have you run the `parse-files` command?');

			return 1;
		}

		$software = $this->getApplication()->getConsoleInput()->getArgument('software');
		$version  = $this->getApplication()->getConsoleInput()->getArgument('version');

		switch ($software)
		{
			case 'cms':
				if (!isset(self::STABLE_RELEASES['cms'][$version]))
				{
					$symfonyStyle->error("Unknown CMS version '$version'");

					return 1;
				}

				$softwareVersion = self::STABLE_RELEASES['cms'][$version];

				break;

			default:
				$symfonyStyle->error("Unknown software package '$software'");

				return 1;
		}

		$versionModel = Version::with(['software'])
			->where('version', '=', $version)
			->firstOrFail();

		$importData = json_decode(file_get_contents($this->dataFile), true);

		$this->importer->importData($importData, $versionModel);

		$symfonyStyle->success('Data imported');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('import-data');
		$this->setDescription('Import the data dump for a given release');
		$this->addArgument('software', InputArgument::REQUIRED, 'The software package to process');
		$this->addArgument('version', InputArgument::REQUIRED, 'The software version to process');
	}
}
