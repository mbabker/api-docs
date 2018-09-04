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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Model defining a PHP class.
 *
 * @property  integer                $id
 * @property  string                 $name
 * @property  string                 $namespace
 * @property  string                 $shortname
 * @property  string                 $summary
 * @property  string                 $description
 * @property  boolean                $final
 * @property  boolean                $abstract
 * @property  PHPClass               $parent
 * @property  integer|null           $parent_id
 * @property  Version                $version
 * @property  integer|null           $version_id
 * @property  Collection|PHPClass[]  $children
 */
final class PHPClass extends Model
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
		'final',
		'abstract',
	];

	/**
	 * The table associated with the model.
	 *
	 * @var  string
	 */
	protected $table = 'classes';

	/**
	 * Defines the relationship for a PHP class to its direct subclasses.
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(static::class);
	}

	/**
	 * Defines the relationship for a PHP class to its parent.
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(static::class);
	}

	/**
	 * Defines the relationship for a PHP class to the software version it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function version(): BelongsTo
	{
		return $this->belongsTo(Version::class);
	}
}
