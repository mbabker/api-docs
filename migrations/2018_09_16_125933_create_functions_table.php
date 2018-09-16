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
 * Create the classes table
 */
class CreateFunctionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		$this->getSchemaBuilder()->create(
			'functions',
			function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name');
				$table->string('namespace')->nullable();
				$table->string('shortname');
				$table->text('summary');
				$table->text('description');

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
		$this->getSchemaBuilder()->dropIfExists('functions');
	}
}
