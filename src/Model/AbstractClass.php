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
 * Abstract model defining a class-like object.
 *
 * @property  integer       $id
 * @property  string        $name
 * @property  string        $namespace
 * @property  string        $shortname
 * @property  string        $summary
 * @property  string        $description
 * @property  Deprecation   $deprecation
 * @property  Version       $version
 * @property  integer|null  $version_id
 */
abstract class AbstractClass extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var  boolean
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $fillable = [
		'name',
		'namespace',
		'shortname',
		'summary',
		'description',
	];

	/**
	 * Defines the relationship for a class to its deprecation.
	 *
	 * @return  MorphOne
	 */
	public function deprecation(): MorphOne
	{
		return $this->morphOne(Deprecation::class, 'deprecatable');
	}

	/**
	 * Defines the relationship for a class to the software version it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function version(): BelongsTo
	{
		return $this->belongsTo(Version::class);
	}
}
