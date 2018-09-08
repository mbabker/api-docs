<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Joomla\ApiDocumentation\Model\ClassMethod;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPClass;

/**
 * Repository for a ClassMethod model.
 */
final class ClassMethodRepository
{
	/**
	 * Create or update a ClassMethod model based on a method node of the parsed data.
	 *
	 * @param   array     $methodNode  The method node to process.
	 * @param   PHPClass  $class       The class to assign this method node to.
	 *
	 * @return  ClassMethod
	 */
	public function createOrUpdateFromMethodNode(array $methodNode, PHPClass $class): ClassMethod
	{
		/** @var ClassMethod $methodModel */
		$methodModel = ClassMethod::with(['deprecation'])
			->whereHas(
				'parent',
				function (Builder $query) use ($class)
				{
					$query->where('id', '=', $class->id);
				}
			)
			->firstOrNew(
				[
					'name' => $methodNode['name'],
				]
			);

		$methodModel->parent()->associate($class);

		$methodModel->fill(
			[
				'summary'     => $methodNode['docblock']['summary'] ?? '',
				'description' => $methodNode['docblock']['description'] ?? '',
				'final'       => $methodNode['final'],
				'abstract'    => $methodNode['abstract'],
				'static'      => $methodNode['static'],
				'visibility'  => $methodNode['visibility'],
			]
		);

		$methodModel->save();

		// Process tags for a deprecation if one exists
		if (isset($methodNode['docblock']['tags']))
		{
			foreach ($methodNode['docblock']['tags'] as $tagNode)
			{
				if ($tagNode['name'] !== 'deprecated')
				{
					continue;
				}

				/** @var Deprecation $deprecationModel */
				$deprecationModel = $methodModel->deprecation ?: Deprecation::make();

				$deprecationModel->fill(
					[
						'description'     => $tagNode['description'],
						'removal_version' => $tagNode['version'],
					]
				);

				$deprecationModel->deprecatable()->associate($methodModel);

				$deprecationModel->save();
			}
		}

		return $methodModel;
	}
}
