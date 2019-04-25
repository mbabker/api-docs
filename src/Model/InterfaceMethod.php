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
 * Model defining an interface method.
 *
 * @property  PHPInterface  $parent
 * @property  integer|null  $parent_id
 */
final class InterfaceMethod extends AbstractMethod
{
	/**
	 * Defines the relationship for an interface method to its parent interface.
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(PHPInterface::class);
	}
}
