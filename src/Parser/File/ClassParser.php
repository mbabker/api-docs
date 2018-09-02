<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\File;

use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\ClassReflector\ConstantReflector;
use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use phpDocumentor\Reflection\ClassReflector\PropertyReflector;
use phpDocumentor\Reflection\FunctionReflector\ArgumentReflector;

/**
 * Parser for a class element.
 */
final class ClassParser
{
	/**
	 * Parse the class element.
	 *
	 * @param   ClassReflector  $reflector  The class to be parsed.
	 *
	 * @return  array
	 */
	public function parse(ClassReflector $reflector): array
	{
		return [
			'name'       => $reflector->getShortName(),
			'namespace'  => $reflector->getNamespace(),
			'final'      => $reflector->isFinal(),
			'abstract'   => $reflector->isAbstract(),
			'extends'    => $reflector->getParentClass(),
			'implements' => $reflector->getInterfaces(),
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
	 * Parse the class constants.
	 *
	 * @param   ConstantReflector[]  $constants  The class constants to be parsed.
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
	 * Parse the class methods.
	 *
	 * @param   MethodReflector[]  $methods  The class methods to be parsed.
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
				'namespace'  => $method->getNamespace(),
				'aliases'    => $method->getNamespaceAliases(),
				'final'      => $method->isFinal(),
				'abstract'   => $method->isAbstract(),
				'static'     => $method->isStatic(),
				'visibility' => $method->getVisibility(),
				'arguments'  => $this->parseArguments($method->getArguments()),
				'docblock'   => (new DocBlockParser)->parse($method),
			];
		}

		return $methodData;
	}

	/**
	 * Parse the class properties.
	 *
	 * @param   PropertyReflector[]  $properties  The class properties to be parsed.
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
