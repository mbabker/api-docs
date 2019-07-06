<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;

/**
 * Extended database service provider
 */
final class DatabaseProvider extends DatabaseServiceProvider
{
	/**
	 * Bootstrap the application events.
	 *
	 * @return  void
	 */
	public function boot()
	{
		// Set up the database's capsule
		(new Manager($this->app))->setAsGlobal();

		Model::setConnectionResolver($this->app['db']);

		// Set the default key length to account for utf8mb4 and MySQL 5.7 quirks
		Builder::defaultStringLength(191);
	}
}
