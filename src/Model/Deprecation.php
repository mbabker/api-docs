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
 * Model defining a deprecation of an element.
 *
 * @property  integer  $id
 * @property  string   $description
 * @property  string   $removal_version
 */
final class Deprecation extends Model
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
		'description',
		'removal_version',
	];

	/**
	 * Defines the relationship for a deprecation to the deprecatable element.
	 *
	 * @return  MorphTo
	 */
	public function deprecatable(): MorphTo
	{
		return $this->morphTo();
	}
}
