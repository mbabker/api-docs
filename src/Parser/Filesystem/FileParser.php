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
use Joomla\ApiDocumentation\Parser\File\InterfaceParser;
use phpDocumentor\Reflection\FileReflector;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Parser for a single file.
 */
final class FileParser
{
	/**
	 * Class parser.
	 *
	 * @var  ClassParser
	 */
	private $classParser;

	/**
	 * Interface parser.
	 *
	 * @var  InterfaceParser
	 */
	private $interfaceParser;

	/**
	 * Argument parser.
	 *
	 * @var  ArgumentParser
	 */
	private $argumentParser;

	/**
	 * Constant parser.
	 *
	 * @var  ConstantParser
	 */
	private $constantParser;

	/**
	 * DocBlock parser.
	 *
	 * @var  DocBlockParser
	 */
	private $docBlockParser;

	/**
	 * Constructor.
	 *
	 * @param   ClassParser      $classParser      Class parser.
	 * @param   InterfaceParser  $interfaceParser  Interface parser.
	 * @param   ArgumentParser   $argumentParser   Argument parser.
	 * @param   ConstantParser   $constantParser   Constant parser.
	 * @param   DocBlockParser   $docBlockParser   DocBlock parser.
	 */
	public function __construct(
		ClassParser $classParser,
		InterfaceParser $interfaceParser,
		ArgumentParser $argumentParser,
		ConstantParser $constantParser,
		DocBlockParser $docBlockParser
	)
	{
		$this->classParser     = $classParser;
		$this->interfaceParser = $interfaceParser;
		$this->argumentParser  = $argumentParser;
		$this->constantParser  = $constantParser;
		$this->docBlockParser  = $docBlockParser;
	}

	/**
	 * Parses a file.
	 *
	 * @param   string  $file  The file to be parsed.
	 *
	 * @return  array
	 */
	public function parse(string $file): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$reflector = new FileReflector($file);
		$reflector->process();

		$fileData = [
			'docblock'   => $this->docBlockParser->parse($reflector),
			'constants'  => [],
			'functions'  => [],
			'classes'    => [],
			'interfaces' => [],
		];

		foreach ($reflector->getConstants() as $constant)
		{
			$fileData['constants'][] = $this->constantParser->parse($constant);
		}

		foreach ($reflector->getFunctions() as $function)
		{
			$fileData['functions'][] = [
				'name'      => $function->getShortName(),
				'namespace' => $function->getNamespace(),
				'aliases'   => $function->getNamespaceAliases(),
				'arguments' => $this->parseArguments($function->getArguments()),
				'docblock'  => $this->docBlockParser->parse($function),
			];
		}

		foreach ($reflector->getClasses() as $class)
		{
			$fileData['classes'][] = $this->classParser->parse($class);
		}

		foreach ($reflector->getInterfaces() as $interface)
		{
			$fileData['interfaces'][] = $this->interfaceParser->parse($interface);
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
			$argumentData[] = $this->argumentParser->parse($argument);
		}

		return $argumentData;
	}
}
