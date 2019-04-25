<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPInterface;
use Joomla\ApiDocumentation\Model\Version;

/**
 * Repository for a PHPInterface model.
 */
final class InterfaceRepository
{
	/**
	 * Create or update a PHPInterface model based on the interface node of the parsed data.
	 *
	 * @param   array    $interfaceNode  The class node to process.
	 * @param   Version  $version        The version to assign this class node to.
	 *
	 * @return  PHPInterface
	 */
	public function createOrUpdateFromInterfaceNode(array $interfaceNode, Version $version): PHPInterface
	{
		$namespace = $interfaceNode['namespace'] === 'global' ? null : $interfaceNode['namespace'];

		/** @var PHPInterface $interfaceModel */
		$interfaceModel = PHPInterface::with(['deprecation'])
			->whereHas(
				'version',
				function (Builder $query) use ($version)
				{
					$query->where('id', '=', $version->id);
				}
			)
			->firstOrNew(
				[
					'namespace' => $namespace,
					'shortname' => $interfaceNode['name'],
				]
			);

		$interfaceModel->version()->associate($version);

		$interfaceModel->fill(
			[
				'name'        => (string) $namespace . '\\' . $interfaceNode['name'],
				'summary'     => $interfaceNode['docblock']['summary'] ?? '',
				'description' => $interfaceNode['docblock']['description'] ?? '',
			]
		);

		$interfaceModel->save();

		// Associate the parents if they exist
		$parents = new Collection;

		foreach ($interfaceNode['extends'] as $extendsInterface)
		{
			/** @var PHPInterface|null $parentInterface */
			$parentInterface = PHPInterface::query()
				->whereHas(
					'version',
					function (Builder $query) use ($version)
					{
						$query->where('id', '=', $version->id);
					}
				)
				->where('name', '=', $extendsInterface)
				->first();

			if ($parentInterface)
			{
				$parents->add($parentInterface);
			}
		}

		$interfaceModel->parents()->sync($parents);

		// Process tags for a deprecation if one exists
		if (isset($interfaceNode['docblock']['tags']))
		{
			foreach ($interfaceNode['docblock']['tags'] as $tagNode)
			{
				if ($tagNode['name'] !== 'deprecated')
				{
					continue;
				}

				/** @var Deprecation $deprecationModel */
				$deprecationModel = $interfaceModel->deprecation ?: Deprecation::make();

				$deprecationModel->fill(
					[
						'description'     => $tagNode['description'],
						'removal_version' => $tagNode['version'],
					]
				);

				$deprecationModel->deprecatable()->associate($interfaceModel);

				$deprecationModel->save();
			}
		}

		return $interfaceModel;
	}
}
