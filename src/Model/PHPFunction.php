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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Model defining a PHP function.
 *
 * @property  integer                $id
 * @property  string                 $name
 * @property  string                 $namespace
 * @property  string                 $shortname
 * @property  string                 $summary
 * @property  string                 $description
 * @property  array                  $return_types
 * @property  string                 $return_description
 * @property  Deprecation            $deprecation
 * @property  Version                $version
 * @property  integer|null           $version_id
 * @property  Collection|Argument[]  $arguments
 */
final class PHPFunction extends Model
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
	];

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
		'return_types',
		'return_description',
	];

	/**
	 * The table associated with the model.
	 *
	 * @var  string
	 */
	protected $table = 'functions';

	/**
	 * Defines the relationship for a class method to its arguments.
	 *
	 * @return  MorphMany
	 */
	public function arguments(): MorphMany
	{
		return $this->morphMany(Argument::class, 'argumented');
	}

	/**
	 * Defines the relationship for a PHP function to its deprecation.
	 *
	 * @return  MorphOne
	 */
	public function deprecation(): MorphOne
	{
		return $this->morphOne(Deprecation::class, 'deprecatable');
	}

	/**
	 * Defines the relationship for a PHP function to the software version it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function version(): BelongsTo
	{
		return $this->belongsTo(Version::class);
	}
}
