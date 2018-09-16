<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model defining an element's argument.
 *
 * @property  integer  $id
 * @property  string   $name
 * @property  string   $description
 * @property  array    $types
 * @property  string   $default_value
 */
final class Argument extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var  boolean
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array
	 */
	protected $casts = [
		'types' => 'array',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $fillable = [
		'name',
		'description',
		'types',
		'default_value',
	];

	/**
	 * Defines the relationship for an argument to the argumented element.
	 *
	 * @return  MorphTo
	 */
	public function argumented(): MorphTo
	{
		return $this->morphTo();
	}
}
