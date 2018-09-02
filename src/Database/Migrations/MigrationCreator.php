<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Database\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

/**
 * Extended migration creator
 */
final class MigrationCreator extends BaseMigrationCreator
{
	/**
	 * Get the path to the stubs.
	 *
	 * @return  string
	 */
	public function stubPath()
	{
		return __DIR__ . '/stubs';
	}
}
