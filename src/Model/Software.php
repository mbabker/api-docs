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
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model defining a software package.
 *
 * @property  integer               $id
 * @property  string                $name
 * @property  string                $slug
 * @property  Collection|Version[]  $versions
 */
final class Software extends Model
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
		'slug',
	];

	/**
	 * Defines the relationship for a software package to the versions it has.
	 *
	 * @return  HasMany
	 */
	public function versions(): HasMany
	{
		return $this->hasMany(Version::class);
	}
}
