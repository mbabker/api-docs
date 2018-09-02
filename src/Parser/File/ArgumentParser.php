<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\File;

use phpDocumentor\Reflection\FunctionReflector\ArgumentReflector;

/**
 * Parser for an argument element.
 */
final class ArgumentParser
{
	/**
	 * Parse the argument element.
	 *
	 * @param   ArgumentReflector  $reflector  The argument to be parsed.
	 *
	 * @return  array
	 */
	public function parse(ArgumentReflector $reflector): array
	{
		return [
			'name'    => $reflector->getName(),
			'default' => $reflector->getDefault(),
			'type'    => $reflector->getType(),
		];
	}
}
