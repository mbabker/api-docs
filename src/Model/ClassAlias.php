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

/**
 * Model defining a class alias.
 *
 * @property  integer       $id
 * @property  string        $old_class
 * @property  string        $new_class
 * @property  string        $deprecation_version
 * @property  Version       $version
 * @property  integer|null  $version_id
 */
final class ClassAlias extends Model
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
		'old_class',
		'new_class',
		'deprecation_version',
	];

	/**
	 * Defines the relationship for a class alias to the software version it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function version(): BelongsTo
	{
		return $this->belongsTo(Version::class);
	}
}
