<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension integrating miscellaneous PHP functions into Twig
 */
final class PhpExtension extends AbstractExtension
{
	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return  TwigFilter[]  An array of filters
	 */
	public function getFilters()
	{
		return [
			new TwigFilter('basename', 'basename'),
			new TwigFilter('get_class', 'get_class'),
		];
	}
}
