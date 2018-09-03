<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\Filesystem;

use Joomla\ApiDocumentation\Parser\File\ArgumentParser;
use Joomla\ApiDocumentation\Parser\File\ClassParser;
use Joomla\ApiDocumentation\Parser\File\ConstantParser;
use Joomla\ApiDocumentation\Parser\File\DocBlockParser;
use phpDocumentor\Reflection\FileReflector;

/**
 * Parser for a single file.
 */
final class FileParser
{
	/**
	 * Parses a file.
	 *
	 * @param   string  $file      The directory to be parsed.
	 * @param   string  $rootPath  The root path of the Joomla installation.
	 *
	 * @return  array
	 */
	public function parse(string $file, string $rootPath): array
	{
		$reflector = new FileReflector($file);
		$reflector->setFilename(ltrim(substr($file, strlen($rootPath)), DIRECTORY_SEPARATOR));
		$reflector->process();

		$fileData = [
			'docblock'  => (new DocBlockParser)->parse($reflector),
			'constants' => [],
			'functions' => [],
			'classes'   => [],
		];

		foreach ($reflector->getConstants() as $constant)
		{
			$fileData['constants'][] = (new ConstantParser)->parse($constant);
		}

		foreach ($reflector->getFunctions() as $function)
		{
			$fileData['functions'][] = [
				'name'      => $function->getShortName(),
				'namespace' => $function->getNamespace(),
				'aliases'   => $function->getNamespaceAliases(),
				'arguments' => $this->parseArguments($function->getArguments()),
				'docblock'  => (new DocBlockParser)->parse($function),
			];
		}

		foreach ($reflector->getClasses() as $class)
		{
			$fileData['classes'][] = (new ClassParser)->parse($class);
		}

		return $fileData;
	}

	/**
	 * Parse a function's arguments.
	 *
	 * @param   ArgumentReflector[]  $arguments  The function arguments to be parsed.
	 *
	 * @return  array
	 */
	private function parseArguments(array $arguments): array
	{
		$argumentData = [];

		foreach ($arguments as $argument)
		{
			$argumentData[] = (new ArgumentParser)->parse($argument);
		}

		return $argumentData;
	}
}