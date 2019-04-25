<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model defining a class method.
 *
 * @property  boolean       $final
 * @property  boolean       $abstract
 * @property  PHPClass      $parent
 * @property  integer|null  $parent_id
 */
final class ClassMethod extends AbstractMethod
{
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
	 * Defines the relationship for a class method to its parent class.
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(PHPClass::class);
	}
}
