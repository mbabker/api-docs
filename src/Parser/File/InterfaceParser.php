<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\File;

use phpDocumentor\Reflection\ClassReflector\ConstantReflector;
use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use phpDocumentor\Reflection\ClassReflector\PropertyReflector;
use phpDocumentor\Reflection\FunctionReflector\ArgumentReflector;
use phpDocumentor\Reflection\InterfaceReflector;

/**
 * Parser for an interface element.
 */
final class InterfaceParser
{
	/**
	 * Parse the class element.
	 *
	 * @param   InterfaceReflector  $reflector  The class to be parsed.
	 *
	 * @return  array
	 */
	public function parse(InterfaceReflector $reflector): array
	{
		return [
			'name'       => $reflector->getShortName(),
			'namespace'  => $reflector->getNamespace(),
			'extends'    => $reflector->getParentInterfaces(),
			'constants'  => $this->parseConstants($reflector->getConstants()),
			'properties' => $this->parseProperties($reflector->getProperties()),
			'methods'    => $this->parseMethods($reflector->getMethods()),
			'docblock'   => (new DocBlockParser)->parse($reflector),
		];
	}

	/**
	 * Parse a method's arguments.
	 *
	 * @param   ArgumentReflector[]  $arguments  The method arguments to be parsed.
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

	/**
	 * Parse the interface constants.
	 *
	 * @param   ConstantReflector[]  $constants  The interface constants to be parsed.
	 *
	 * @return  array
	 */
	private function parseConstants(array $constants): array
	{
		$constantData = [];

		foreach ($constants as $constant)
		{
			$constantData[] = (new ConstantParser)->parse($constant);
		}

		return $constantData;
	}

	/**
	 * Parse the interface methods.
	 *
	 * @param   MethodReflector[]  $methods  The interface methods to be parsed.
	 *
	 * @return  array
	 */
	private function parseMethods(array $methods): array
	{
		$methodData = [];

		foreach ($methods as $method)
		{
			$methodData[] = [
				'name'       => $method->getShortName(),
				'aliases'    => $method->getNamespaceAliases(),
				'static'     => $method->isStatic(),
				'visibility' => $method->getVisibility(),
				'arguments'  => $this->parseArguments($method->getArguments()),
				'docblock'   => (new DocBlockParser)->parse($method),
			];
		}

		return $methodData;
	}

	/**
	 * Parse the interface properties.
	 *
	 * @param   PropertyReflector[]  $properties  The interface properties to be parsed.
	 *
	 * @return  array
	 */
	private function parseProperties(array $properties): array
	{
		$propertyData = [];

		foreach ($properties as $property)
		{
			$propertyData[] = [
				'name'       => $property->getName(),
				'static'     => $property->isStatic(),
				'visibility' => $property->getVisibility(),
				'docblock'   => (new DocBlockParser)->parse($property),
			];
		}

		return $propertyData;
	}
}
