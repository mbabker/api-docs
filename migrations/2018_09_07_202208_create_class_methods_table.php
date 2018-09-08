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
 * Create the class_methods table
 */
class CreateClassMethodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		$this->getSchemaBuilder()->create(
			'class_methods',
			function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('name');
				$table->text('summary');
				$table->text('description');
				$table->boolean('final')->default(false);
				$table->boolean('abstract')->default(false);
				$table->boolean('static')->default(false);
				$table->string('visibility');

				$table->integer('parent_id')->nullable()->unsigned()->index();
				$table->foreign('parent_id')->references('id')->on('classes')->onDelete('cascade');
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
		$this->getSchemaBuilder()->dropIfExists('class_methods');
	}
}
