<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Joomla\ApiDocumentation\Model\Argument;
use Joomla\ApiDocumentation\Model\InterfaceMethod;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPInterface;

/**
 * Repository for a InterfaceMethod model.
 */
final class InterfaceMethodRepository
{
	/**
	 * Create or update a InterfaceMethod model based on a method node of the parsed data.
	 *
	 * @param   array         $methodNode  The method node to process.
	 * @param   PHPInterface  $interface   The interface to assign this method node to.
	 *
	 * @return  InterfaceMethod
	 */
	public function createOrUpdateFromMethodNode(array $methodNode, PHPInterface $interface): InterfaceMethod
	{
		/** @var InterfaceMethod $methodModel */
		$methodModel = InterfaceMethod::with(['deprecation'])
			->whereHas(
				'parent',
				function (Builder $query) use ($interface)
				{
					$query->where('id', '=', $interface->id);
				}
			)
			->firstOrNew(
				[
					'name' => $methodNode['name'],
				]
			);

		$methodModel->parent()->associate($interface);

		$methodModel->fill(
			[
				'summary'     => $methodNode['docblock']['summary'] ?? '',
				'description' => $methodNode['docblock']['description'] ?? '',
				'static'      => $methodNode['static'],
				'visibility'  => $methodNode['visibility'],
			]
		);

		$methodModel->save();

		// Process docblock tags if present
		if (isset($methodNode['docblock']['tags']))
		{
			foreach ($methodNode['docblock']['tags'] as $tagNode)
			{
				if (!isset($tagNode['name']))
				{
					continue;
				}

				switch ($tagNode['name'])
				{
					case 'deprecated':
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

						break;

					case 'return':
						$methodModel->fill(
							[
								'return_types'       => $tagNode['types'],
								'return_description' => $tagNode['description'],
							]
						);

						$methodModel->save();

						break;

					default:
						// Unknown or unsupported tag
						break;
				}
			}
		}

		// Process arguments
		foreach ($methodNode['arguments'] as $argumentNode)
		{
			/** @var Argument $argumentModel */
			$argumentModel = $methodModel->arguments()
				->firstOrNew(
					[
						'name' => $argumentNode['name'],
					]
				);

			// Fill data from the argument node and set defaults for values which come from the tags
			$argumentModel->fill(
				[
					'default_value' => $argumentNode['default'],
					'description'   => '',
					'types'         => [],
				]
			);

			// Fill extra data from param tag
			if (isset($methodNode['docblock']['tags']))
			{
				foreach ($methodNode['docblock']['tags'] as $tagNode)
				{
					if ($tagNode['name'] !== 'param')
					{
						continue;
					}

					if (!isset($tagNode['variable']) || $tagNode['variable'] !== $argumentNode['name'])
					{
						continue;
					}

					$types = $tagNode['types'];

					// Add type from argument if available and not already included
					if ($argumentNode['type'] !== '' && !in_array($argumentNode['type'], $types, true))
					{
						$types[] = $argumentNode['type'];
					}

					$argumentModel->fill(
						[
							'description' => $tagNode['description'],
							'types'       => $types,
						]
					);

					// We can break the loop safely
					break;
				}
			}

			$argumentModel->argumented()->associate($methodModel);

			$argumentModel->save();
		}

		return $methodModel;
	}
}
