<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

use Illuminate\Database\Schema\Blueprint;
use Joomla\ApiDocumentation\Database\Migrations\Migration;

/**
 * Create the deprecations table
 */
class CreateArgumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		$this->getSchemaBuilder()->create(
			'arguments',
			function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name');
				$table->text('description');
				$table->text('types');
				$table->string('default_value')->nullable();
				$table->integer('argumented_id');
				$table->string('argumented_type');
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return  void
	 */
	public function down()
	{
		$this->getSchemaBuilder()->dropIfExists('arguments');
	}
}
