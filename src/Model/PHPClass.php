<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model defining a PHP class.
 *
 * @property  boolean                     $final
 * @property  boolean                     $abstract
 * @property  PHPClass                    $parent
 * @property  integer|null                $parent_id
 * @property  Collection|PHPClass[]       $children
 * @property  Collection|PHPInterface[]   $implements
 * @property  Collection|ClassMethod[]    $methods
 * @property  Collection|ClassProperty[]  $properties
 */
final class PHPClass extends AbstractClass
{
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
	 * Defines the relationship for a PHP class to the interfaces it implements.
	 *
	 * @return  BelongsToMany
	 */
	public function implements(): BelongsToMany
	{
		return $this->belongsToMany(PHPInterface::class, 'class_interface', 'class_id', 'interface_id');
	}

	/**
	 * Defines the relationship for a PHP class to its methods.
	 *
	 * @return  HasMany
	 */
	public function methods(): HasMany
	{
		return $this->hasMany(ClassMethod::class);
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
	 * Defines the relationship for a PHP class to its properties.
	 *
	 * @return  HasMany
	 */
	public function properties(): HasMany
	{
		return $this->hasMany(ClassProperty::class);
	}
}
