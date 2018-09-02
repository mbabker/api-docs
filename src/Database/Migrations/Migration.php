<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Database\Migrations;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Builder;

/**
 * Base migration class
 */
abstract class Migration extends BaseMigration
{
	/**
	 * Get the database connection
	 *
	 * @return  Connection
	 */
	protected function getDatabaseConnection(): Connection
	{
		return Manager::connection($this->getConnection());
	}

	/**
	 * Get the schema builder
	 *
	 * @return  Builder
	 */
	protected function getSchemaBuilder(): Builder
	{
		return Manager::schema($this->getConnection());
	}
}
