<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Twig;

use Joomla\ApiDocumentation\Twig\Service\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig extension integrating routing capabilities for the application
 */
final class RoutingExtension extends AbstractExtension
{
	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return  TwigFilter[]  An array of filters.
	 */
	public function getFilters()
	{
		return [
			new TwigFilter('routed_namespace', [$this, 'makeRoutableNamespace']),
		];
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return  TwigFunction[]  An array of functions.
	 */
	public function getFunctions()
	{
		return [
			new TwigFunction('route', [Router::class, 'getRouteUri']),
			new TwigFunction('url', [Router::class, 'getRouteUrl']),
		];
	}

	/**
	 * Converts the namespace to a routable URI segment
	 *
	 * @param   string  $namespace  The namespace to route
	 *
	 * @return  string
	 */
	public function makeRoutableNamespace(string $namespace): string
	{
		// Replace namespace separators with slashes
		$routedNamespace = str_replace('\\', '/', $namespace);

		return $routedNamespace;
	}
}
