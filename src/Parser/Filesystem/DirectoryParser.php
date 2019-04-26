<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\Filesystem;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Parser for a directory of files.
 */
final class DirectoryParser
{
	/**
	 * File parser.
	 *
	 * @var  FileParser
	 */
	private $fileParser;

	/**
	 * Constructor.
	 *
	 * @param   FileParser  $fileParser  File parser.
	 */
	public function __construct(FileParser $fileParser)
	{
		$this->fileParser = $fileParser;
	}

	/**
	 * Parses all files in a directory.
	 *
	 * @param   string  $directory  The directory to be parsed.
	 * @param   string  $rootPath   The root path of the Joomla installation.
	 *
	 * @return  array
	 */
	public function parse(string $directory, string $rootPath): array
	{
		$data = [];

		/** @var SplFileInfo $file */
		foreach ($this->getDirectoryFileList($directory) as $file)
		{
			$data[ltrim(substr($file->getPathname(), strlen($rootPath)), DIRECTORY_SEPARATOR)] = $this->fileParser->parse($file->getPathname());
		}

		return $data;
	}

	/**
	 * Get the list of files in a directory to be parsed.
	 *
	 * @param   string  $directory  The directory to get the file list for.
	 *
	 * @return  Finder
	 */
	private function getDirectoryFileList(string $directory): Finder
	{
		return (new Finder)
			->ignoreDotFiles(true)
			->ignoreVCS(true)
			->files()
			->name('*.php')
			->in($directory);
	}
}
