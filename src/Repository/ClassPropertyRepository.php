<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Joomla\ApiDocumentation\Model\ClassProperty;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPClass;

/**
 * Repository for a ClassProperty model.
 */
final class ClassPropertyRepository
{
	/**
	 * Create or update a ClassProperty model based on a property node of the parsed data.
	 *
	 * @param   array     $propertyNode  The property node to process.
	 * @param   PHPClass  $class         The class to assign this property node to.
	 *
	 * @return  ClassProperty
	 */
	public function createOrUpdateFromPropertyNode(array $propertyNode, PHPClass $class): ClassProperty
	{
		/** @var ClassProperty $propertyModel */
		$propertyModel = ClassProperty::with(['deprecation'])
			->whereHas(
				'parent',
				function (Builder $query) use ($class)
				{
					$query->where('id', '=', $class->id);
				}
			)
			->firstOrNew(
				[
					'name' => $propertyNode['name'],
				]
			);

		$propertyModel->parent()->associate($class);

		$propertyModel->fill(
			[
				'summary'     => $propertyNode['docblock']['summary'] ?? '',
				'description' => $propertyNode['docblock']['description'] ?? '',
				'static'      => $propertyNode['static'],
				'visibility'  => $propertyNode['visibility'],
			]
		);

		$propertyModel->save();

		// Process tags for a deprecation if one exists
		if (isset($propertyNode['docblock']['tags']))
		{
			foreach ($propertyNode['docblock']['tags'] as $tagNode)
			{
				if ($tagNode['name'] !== 'deprecated')
				{
					continue;
				}

				/** @var Deprecation $deprecationModel */
				$deprecationModel = $propertyModel->deprecation ?: Deprecation::make();

				$deprecationModel->fill(
					[
						'description'     => $tagNode['description'],
						'removal_version' => $tagNode['version'],
					]
				);

				$deprecationModel->deprecatable()->associate($propertyModel);

				$deprecationModel->save();

				// We can break the loop safely
				break;
			}
		}

		return $propertyModel;
	}
}
