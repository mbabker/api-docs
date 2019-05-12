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
 * Create the class_aliases table
 */
class CreateClassAliasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		$this->getSchemaBuilder()->create(
			'class_aliases',
			function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('old_class');
				$table->string('new_class');
				$table->string('deprecation_version');

				$table->integer('version_id')->nullable()->unsigned()->index();
				$table->foreign('version_id')->references('id')->on('versions')->onDelete('cascade');
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
		$this->getSchemaBuilder()->dropIfExists('class_aliases');
	}
}
