<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Kernel;

use Joomla\ApiDocumentation\Kernel;
use Joomla\Application\AbstractApplication;
use Joomla\Console\Application;
use Joomla\DI\Container;

/**
 * Console Kernel
 */
final class ConsoleKernel extends Kernel
{
	/**
	 * Build the service container
	 *
	 * @return  Container
	 *
	 * @throws  \InvalidArgumentException
	 */
	protected function buildContainer(): Container
	{
		$container = parent::buildContainer();

		// Alias the console application to Joomla's base application class as this is the primary application for the environment
		$container->alias(AbstractApplication::class, Application::class);

		return $container;
	}
}
