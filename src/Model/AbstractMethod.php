<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Abstract model defining a method on a class-like object.
 *
 * @property  integer                $id
 * @property  string                 $name
 * @property  string                 $summary
 * @property  string                 $description
 * @property  array                  $return_types
 * @property  string                 $return_description
 * @property  boolean                $static
 * @property  boolean                $visibility
 * @property  Deprecation            $deprecation
 * @property  Collection|Argument[]  $arguments
 */
abstract class AbstractMethod extends Model
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
		'return_types' => 'array',
		'static'       => 'boolean',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $fillable = [
		'name',
		'summary',
		'description',
		'return_types',
		'return_description',
		'static',
		'visibility',
	];

	/**
	 * Defines the relationship for a method to its arguments.
	 *
	 * @return  MorphMany
	 */
	public function arguments(): MorphMany
	{
		return $this->morphMany(Argument::class, 'argumented');
	}

	/**
	 * Defines the relationship for a method to its deprecation.
	 *
	 * @return  MorphOne
	 */
	public function deprecation(): MorphOne
	{
		return $this->morphOne(Deprecation::class, 'deprecatable');
	}
}
