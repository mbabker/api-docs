<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Repository;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Joomla\ApiDocumentation\Model\Version;

/**
 * Repository for a Version model.
 */
final class VersionRepository
{
	/**
	 * Find the version record for a software version.
	 *
	 * @param   string  $software  The software to look up.
	 * @param   string  $version   The version of software to look up.
	 *
	 * @return  Version
	 *
	 * @throws  ModelNotFoundException
	 */
	public function findSoftwareVersionOrFail(string $software, string $version): Version
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return Version::query()
			->where('software', '=', $software)
			->where('version', '=', $version)
			->firstOrFail();
	}
}
