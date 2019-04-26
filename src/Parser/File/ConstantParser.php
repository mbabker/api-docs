<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\File;

use phpDocumentor\Reflection\ConstantReflector;

/**
 * Parser for a constant element.
 */
final class ConstantParser
{
	/**
	 * DocBlock parser.
	 *
	 * @var  DocBlockParser
	 */
	private $docBlockParser;

	/**
	 * Constructor.
	 *
	 * @param   DocBlockParser  $docBlockParser  DocBlock parser.
	 */
	public function __construct(DocBlockParser $docBlockParser)
	{
		$this->docBlockParser = $docBlockParser;
	}

	/**
	 * Parse the constant element.
	 *
	 * @param   ConstantReflector  $reflector  The constant to be parsed.
	 *
	 * @return  array
	 */
	public function parse(ConstantReflector $reflector): array
	{
		return [
			'name'     => $reflector->getShortName(),
			'docblock' => $this->docBlockParser->parse($reflector),
		];
	}
}
