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
 * Model defining a version of software.
 *
 * @property  integer       $id
 * @property  string        $version
 * @property  Software      $software
 * @property  integer|null  $software_id
 */
final class Version extends Model
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
		'version',
	];

	/**
	 * Defines the relationship for a version to the software it belongs to.
	 *
	 * @return  BelongsTo
	 */
	public function software(): BelongsTo
	{
		return $this->belongsTo(Software::class);
	}
}
