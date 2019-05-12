<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\Builder;
use Joomla\ApiDocumentation\Model\ClassAlias;
use Joomla\ApiDocumentation\Model\Version;

/**
 * Repository for a ClassAlias model.
 */
final class ClassAliasRepository
{
	/**
	 * Create or update a ClassAlias model based on an alias node of the parsed data.
	 *
	 * @param   array    $aliasNode  The method node to process.
	 * @param   Version  $version    The version to assign this class node to.
	 *
	 * @return  ClassAlias
	 */
	public function createOrUpdateFromAliasNode(array $aliasNode, Version $version): ClassAlias
	{
		/** @var ClassAlias $aliasModel */
		$aliasModel = ClassAlias::query()
			->whereHas(
				'version',
				function (Builder $query) use ($version)
				{
					$query->where('id', '=', $version->id);
				}
			)
			->firstOrNew(
				[
					'old_class' => $aliasNode['alias'],
					'new_class' => $aliasNode['original'],
				]
			);

		$aliasModel->version()->associate($version);

		$aliasModel->fill(
			[
				'deprecation_version' => $aliasNode['version'] ?? '',
			]
		);

		$aliasModel->save();

		return $aliasModel;
	}
}
