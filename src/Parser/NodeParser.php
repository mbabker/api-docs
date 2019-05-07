<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser;

use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use phpDocumentor\Reflection\ClassReflector\PropertyReflector;
use phpDocumentor\Reflection\ConstantReflector;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag\LinkTag;
use phpDocumentor\Reflection\DocBlock\Tag\ParamTag;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use phpDocumentor\Reflection\DocBlock\Tag\SeeTag;
use phpDocumentor\Reflection\DocBlock\Tag\VersionTag;
use phpDocumentor\Reflection\FunctionReflector;
use phpDocumentor\Reflection\FunctionReflector\ArgumentReflector;
use phpDocumentor\Reflection\InterfaceReflector;
use phpDocumentor\Reflection\ReflectionAbstract;

/**
 * Parses PHP elements into the application's data structure.
 */
final class NodeParser
{
	/**
	 * Parse an argument element.
	 *
	 * @param   ArgumentReflector  $reflector  The argument to be parsed.
	 *
	 * @return  array
	 */
	public function parseArgument(ArgumentReflector $reflector): array
	{
		return [
			'name'    => $reflector->getName(),
			'default' => $reflector->getDefault(),
			'type'    => $reflector->getType(),
		];
	}

	/**
	 * Parse a class element.
	 *
	 * @param   ClassReflector  $reflector  The class to be parsed.
	 *
	 * @return  array
	 */
	public function parseClass(ClassReflector $reflector): array
	{
		return [
			'name'       => $reflector->getShortName(),
			'namespace'  => $reflector->getNamespace(),
			'final'      => $reflector->isFinal(),
			'abstract'   => $reflector->isAbstract(),
			'extends'    => $reflector->getParentClass(),
			'implements' => $reflector->getInterfaces(),
			'constants'  => $this->parseConstants($reflector->getConstants()),
			'properties' => $this->parseClassProperties($reflector->getProperties()),
			'methods'    => $this->parseMethods($reflector->getMethods(), 'class'),
			'docblock'   => $this->parseDocBlock($reflector),
		];
	}

	/**
	 * Parse a constant element.
	 *
	 * @param   ConstantReflector  $reflector  The constant to be parsed.
	 *
	 * @return  array
	 */
	public function parseConstant(ConstantReflector $reflector): array
	{
		return [
			'name'     => $reflector->getShortName(),
			'docblock' => $this->parseDocBlock($reflector),
		];
	}

	/**
	 * Parse the docblock for an element.
	 *
	 * @param   ReflectionAbstract  $reflector  The element whose docblock is to be parsed.
	 *
	 * @return  array
	 */
	public function parseDocBlock(ReflectionAbstract $reflector): array
	{
		/** @var DocBlock $docblock */
		$docblock = $reflector->getDocBlock();

		if (!$docblock)
		{
			return [];
		}

		$data = [
			'summary'     => $docblock->getShortDescription(),
			'description' => $docblock->getLongDescription()->getFormattedContents(),
			'tags'        => [],
		];

		foreach ($docblock->getTags() as $tag)
		{
			$tagData = [
				'name'        => $tag->getName(),
				'description' => $tag->getDescription(),
			];

			if ($tag instanceof ReturnTag)
			{
				$tagData['types'] = $tag->getTypes();
			}

			if ($tag instanceof LinkTag)
			{
				$tagData['link'] = $tag->getLink();
			}

			if ($tag instanceof ParamTag)
			{
				$tagData['variable'] = $tag->getVariableName();
			}

			if ($tag instanceof SeeTag)
			{
				$tagData['refers'] = $tag->getReference();
			}

			if ($tag instanceof VersionTag)
			{
				$tagData['version'] = $tag->getVersion();
			}

			$data['tags'][] = $tagData;
		}

		return $data;
	}

	/**
	 * Parse a function element.
	 *
	 * @param   FunctionReflector  $reflector  The function to be parsed.
	 *
	 * @return  array
	 */
	public function parseFunction(FunctionReflector $reflector): array
	{
		return [
			'name'      => $reflector->getShortName(),
			'namespace' => $reflector->getNamespace(),
			'aliases'   => $reflector->getNamespaceAliases(),
			'arguments' => $this->parseArguments($reflector->getArguments()),
			'docblock'  => $this->parseDocBlock($reflector),
		];
	}

	/**
	 * Parse an interface element.
	 *
	 * @param   InterfaceReflector  $reflector  The interface to be parsed.
	 *
	 * @return  array
	 */
	public function parseInterface(InterfaceReflector $reflector): array
	{
		return [
			'name'       => $reflector->getShortName(),
			'namespace'  => $reflector->getNamespace(),
			'extends'    => $reflector->getParentInterfaces(),
			'constants'  => $this->parseConstants($reflector->getConstants()),
			'properties' => $this->parseClassProperties($reflector->getProperties()),
			'methods'    => $this->parseMethods($reflector->getMethods(), 'interface'),
			'docblock'   => $this->parseDocBlock($reflector),
		];
	}

	/**
	 * Parse a collection of arguments.
	 *
	 * @param   ArgumentReflector[]  $arguments  The arguments to be parsed.
	 *
	 * @return  array
	 */
	private function parseArguments(array $arguments): array
	{
		$argumentData = [];

		foreach ($arguments as $argument)
		{
			$argumentData[] = $this->parseArgument($argument);
		}

		return $argumentData;
	}

	/**
	 * Parse a class' properties.
	 *
	 * @param   PropertyReflector[]  $properties  The class properties to be parsed.
	 *
	 * @return  array
	 */
	private function parseClassProperties(array $properties): array
	{
		$propertyData = [];

		foreach ($properties as $property)
		{
			$propertyData[] = [
				'name'       => $property->getName(),
				'static'     => $property->isStatic(),
				'visibility' => $property->getVisibility(),
				'docblock'   => $this->parseDocBlock($property),
			];
		}

		return $propertyData;
	}

	/**
	 * Parse a collection of constants.
	 *
	 * @param   ConstantReflector[]  $constants  The constants to be parsed.
	 *
	 * @return  array
	 */
	private function parseConstants(array $constants): array
	{
		$constantData = [];

		foreach ($constants as $constant)
		{
			$constantData[] = $this->parseConstant($constant);
		}

		return $constantData;
	}

	/**
	 * Parse a collection of methods.
	 *
	 * @param   MethodReflector[]  $methods         The methods to be parsed.
	 * @param   string             $parentNodeType  The type of parent node being parsed.
	 *
	 * @return  array
	 */
	private function parseMethods(array $methods, string $parentNodeType): array
	{
		$methodData = [];

		foreach ($methods as $method)
		{
			$data = [
				'name'       => $method->getShortName(),
				'aliases'    => $method->getNamespaceAliases(),
				'static'     => $method->isStatic(),
				'visibility' => $method->getVisibility(),
				'arguments'  => $this->parseArguments($method->getArguments()),
				'docblock'   => $this->parseDocBlock($method),
			];

			// Add extra data based on the node type
			switch ($parentNodeType)
			{
				case 'class':
					$data['final']    = $method->isFinal();
					$data['abstract'] = $method->isAbstract();

					break;

				case 'interface':
				default:
					// No extra info
					break;
			}

			$methodData[] = $data;
		}

		return $methodData;
	}
}
