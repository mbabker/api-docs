<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser;

use Joomla\ApiDocumentation\Parser\File\ArgumentParser;
use Joomla\ApiDocumentation\Parser\File\ClassParser;
use Joomla\ApiDocumentation\Parser\File\ConstantParser;
use Joomla\ApiDocumentation\Parser\File\DocBlockParser;
use Joomla\ApiDocumentation\Parser\File\InterfaceParser;
use phpDocumentor\Reflection\FileReflector;
use PhpParser\Lexer\Emulative;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Parser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Parser for files in the filesystem.
 */
final class FilesystemParser
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
	 * Parses a classmap file.
	 *
	 * @param   string  $file      The file to be parsed.
	 * @param   string  $rootPath  The root path of the Joomla installation.
	 *
	 * @return  array
	 *
	 * @note    This method is inspired by the \Akeeba\JTypeHints\Engine\Parser class,
	 *          adapted for this application and the different PHPParser package dependencies.
	 */
	public function parseClassmapFile(string $file, string $rootPath): array
	{
		$aliases = [];
		$parser  = new Parser(new Emulative);

		foreach (new \SplFileObject($file) as $line)
		{
			if (stripos($line, 'JLoader::registerAlias') === false)
			{
				continue;
			}

			try
			{
				$data = $this->parseClassmapFileLine($line, $parser);
			}
			catch (\RuntimeException $exception)
			{
				// Line has errors, continue
				continue;
			}

			// Skip if missing class names
			if (empty($data['alias']) || empty($data['original']))
			{
				continue;
			}

			$aliases[] = $data;
		}

		return $aliases;
	}

	/**
	 * Parses all files in a directory.
	 *
	 * @param   string  $directory  The directory to be parsed.
	 * @param   string  $rootPath   The root path of the Joomla installation.
	 *
	 * @return  array
	 */
	public function parseDirectory(string $directory, string $rootPath): array
	{
		$data = [];

		/** @var SplFileInfo $file */
		foreach ($this->getDirectoryFileList($directory) as $file)
		{
			$data[ltrim(substr($file->getPathname(), strlen($rootPath)), DIRECTORY_SEPARATOR)] = $this->parseFile($file->getPathname());
		}

		return $data;
	}

	/**
	 * Parses a file.
	 *
	 * @param   string  $file  The file to be parsed.
	 *
	 * @return  array
	 */
	public function parseFile(string $file): array
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

	/**
	 * Parses a single line to extract the alias data from the classmap.
	 *
	 * @param   string  $line    The contents of the line to process.
	 * @param   Parser  $parser  The element parser.
	 *
	 * @return  array
	 *
	 * @note    This method is inspired by the \Akeeba\JTypeHints\Engine\Parser class,
	 *          adapted for this application and the different PHPParser package dependencies.
	 */
	private function parseClassmapFileLine(string $line, Parser $parser): array
	{
		$evaluated = $parser->parse('<?php ' . $line);

		if (empty($evaluated))
		{
			throw new \RuntimeException("Not a valid expression statement");
		}

		/** @var StaticCall $expression */
		$expression = $evaluated[0];

		if ($expression->getType() !== 'Expr_StaticCall')
		{
			throw new \RuntimeException("Not a valid static call line");
		}

		if (($expression->class->getFirst() !== 'JLoader') || ($expression->name !== 'registerAlias'))
		{
			throw new \RuntimeException("Not a call to JLoader::registerAlias");
		}

		if ((count($expression->args) < 2) || (count($expression->args) > 3))
		{
			throw new \RuntimeException("Unknown call format to JLoader::registerAlias");
		}

		return [
			'alias'    => ltrim($expression->args[0]->value->value, '\\'),
			'original' => ltrim($expression->args[1]->value->value, '\\'),
			'version'  => isset($expression->args[2]) ? $expression->args[2]->value->value : '4.0',
		];
	}
}
