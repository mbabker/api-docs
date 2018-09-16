<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Joomla\ApiDocumentation\Model\Deprecation;
use Joomla\ApiDocumentation\Model\PHPFunction;
use Joomla\ApiDocumentation\Model\Version;

/**
 * Repository for a PHPFunction model.
 */
final class FunctionRepository
{
	/**
	 * Create or update a PHPFunction model based on a function node of the parsed data.
	 *
	 * @param   array    $functionNode  The function node to process.
	 * @param   Version  $version       The version to assign this function node to.
	 *
	 * @return  PHPFunction
	 */
	public function createOrUpdateFromFunctionNode(array $functionNode, Version $version): PHPFunction
	{
		$namespace = $functionNode['namespace'] === 'global' ? null : $functionNode['namespace'];

		/** @var PHPFunction $functionModel */
		$functionModel = PHPFunction::with(['deprecation'])
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
					'shortname' => $functionNode['name'],
				]
			);

		$functionModel->version()->associate($version);

		$functionModel->fill(
			[
				'name'        => (string) $namespace . '\\' . $functionNode['name'],
				'summary'     => $functionNode['docblock']['summary'] ?? '',
				'description' => $functionNode['docblock']['description'] ?? '',
			]
		);

		$functionModel->save();

		// Process tags for a deprecation if one exists
		if (isset($functionNode['docblock']['tags']))
		{
			foreach ($functionNode['docblock']['tags'] as $tagNode)
			{
				if ($tagNode['name'] !== 'deprecated')
				{
					continue;
				}

				/** @var Deprecation $deprecationModel */
				$deprecationModel = $functionModel->deprecation ?: Deprecation::make();

				$deprecationModel->fill(
					[
						'description'     => $tagNode['description'],
						'removal_version' => $tagNode['version'],
					]
				);

				$deprecationModel->deprecatable()->associate($functionModel);

				$deprecationModel->save();
			}
		}

		// Process arguments
		foreach ($functionNode['arguments'] as $argumentNode)
		{
			/** @var Argument $argumentModel */
			$argumentModel = $functionModel->arguments()
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
			if (isset($functionNode['docblock']['tags']))
			{
				foreach ($functionNode['docblock']['tags'] as $tagNode)
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

			$argumentModel->argumented()->associate($functionModel);

			$argumentModel->save();
		}

		return $functionModel;
	}
}
