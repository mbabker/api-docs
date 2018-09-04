<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Parser\File;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag\LinkTag;
use phpDocumentor\Reflection\DocBlock\Tag\ParamTag;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use phpDocumentor\Reflection\DocBlock\Tag\SeeTag;
use phpDocumentor\Reflection\DocBlock\Tag\VersionTag;
use phpDocumentor\Reflection\ReflectionAbstract;

/**
 * Parser for an element's doc block.
 */
final class DocBlockParser
{
	/**
	 * Parse the docblock for an element.
	 *
	 * @param   ReflectionAbstract  $reflector  The element whose docblock is to be parsed.
	 *
	 * @return  array
	 */
	public function parse(ReflectionAbstract $reflector): array
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
}
