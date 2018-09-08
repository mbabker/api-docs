<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Model defining a class method.
 *
 * @property  integer       $id
 * @property  string        $name
 * @property  string        $summary
 * @property  string        $description
 * @property  boolean       $final
 * @property  boolean       $abstract
 * @property  boolean       $static
 * @property  boolean       $visibility
 * @property  Deprecation   $deprecation
 * @property  PHPClass      $parent
 * @property  integer|null  $parent_id
 */
final class ClassMethod extends Model
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
		'final'    => 'boolean',
		'abstract' => 'boolean',
		'static'   => 'boolean',
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
		'final',
		'abstract',
		'static',
		'visibility',
	];

	/**
	 * Defines the relationship for a class method to its deprecation.
	 *
	 * @return  MorphOne
	 */
	public function deprecation(): MorphOne
	{
		return $this->morphOne(Deprecation::class, 'deprecatable');
	}

	/**
	 * Defines the relationship for a class method to its parent class.
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(static::class);
	}
}
