<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model defining a PHP interface.
 *
 * @property  Collection|PHPInterface[]     $children
 * @property  Collection|PHPClass[]         $implementors
 * @property  Collection|InterfaceMethod[]  $methods
 * @property  Collection|PHPInterface[]     $parents
 */
final class PHPInterface extends AbstractClass
{
	/**
	 * The table associated with the model.
	 *
	 * @var  string
	 */
	protected $table = 'interfaces';

	/**
	 * Defines the relationship for a PHP interface to its child interfaces.
	 *
	 * @return  BelongsToMany
	 */
	public function children(): BelongsToMany
	{
		return $this->belongsToMany(static::class, 'interface_parent', 'parent_id', 'interface_id');
	}

	/**
	 * Defines the relationship for a PHP interface to the classes which implement it.
	 *
	 * @return  BelongsToMany
	 */
	public function implementors(): BelongsToMany
	{
		return $this->belongsToMany(PHPClass::class, 'class_interface', 'interface_id', 'class_id');
	}

	/**
	 * Defines the relationship for a PHP interface to its methods.
	 *
	 * @return  HasMany
	 */
	public function methods(): HasMany
	{
		return $this->hasMany(InterfaceMethod::class);
	}

	/**
	 * Defines the relationship for a PHP interface to its parent interfaces.
	 *
	 * @return  BelongsToMany
	 */
	public function parents(): BelongsToMany
	{
		return $this->belongsToMany(static::class, 'interface_parent', 'interface_id', 'parent_id');
	}
}
