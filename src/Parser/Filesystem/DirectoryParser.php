<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\Filesystem;

/**
 * Parser for a directory of files.
 */
final class DirectoryParser
{
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

		foreach ($this->getDirectoryFileList($directory) as $file)
		{
			$data[ltrim(substr($file, strlen($rootPath)), DIRECTORY_SEPARATOR)] = (new FileParser)->parse($file, $rootPath);
		}

		return $data;
	}

	/**
	 * Get the list of files in a directory to be parsed.
	 *
	 * @param   string  $directory  The directory to get the file list for.
	 *
	 * @return  array
	 */
	private function getDirectoryFileList(string $directory): array
	{
		$iterableFiles = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory)
		);

		$files = [];

		foreach ($iterableFiles as $file)
		{
			if ($file->getExtension() !== 'php')
			{
				continue;
			}

			$files[] = $file->getPathname();
		}

		return $files;
	}
}
