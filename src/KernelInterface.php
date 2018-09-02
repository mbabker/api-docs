<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation;

/**
 * Core Kernel
 */
interface KernelInterface
{
	/**
	 * Boot the Kernel.
	 *
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function boot(): void;

	/**
	 * Run the Kernel.
	 *
	 * @return  void
	 */
	public function run(): void;
}
