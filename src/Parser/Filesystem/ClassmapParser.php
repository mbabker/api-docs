<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\Filesystem;

use PhpParser\Lexer\Emulative;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Parser;

/**
 * Parser for a Joomla release' classmap file.
 *
 * This class is inspired by the \Akeeba\JTypeHints\Engine\Parser class,
 * adapted for this application and the different PHPParser package dependencies.
 */
final class ClassmapParser
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
				$data = $this->parseLine($line, $parser);
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
	 * Parses a single line to extract the alias data from the classmap.
	 *
	 * @param   string  $line    The contents of the line to process.
	 * @param   Parser  $parser  The element parser.
	 *
	 * @return  array
	 */
	private function parseLine(string $line, Parser $parser): array
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
