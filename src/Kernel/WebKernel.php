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
use Joomla\Application\WebApplication;
use Joomla\DI\Container;

/**
 * Web Kernel
 */
final class WebKernel extends Kernel
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

		// Alias the web application to Joomla's base application class as this is the primary application for the environment
		$container->alias(AbstractApplication::class, WebApplication::class);

		return $container;
	}
}
