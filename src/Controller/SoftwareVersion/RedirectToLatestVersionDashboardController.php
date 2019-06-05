<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller\SoftwareVersion;

use Joomla\ApiDocumentation\Controller\AbstractController;
use Joomla\Router\Exception\RouteNotFoundException;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Controller class to redirect a request for a software without a version to the version dashboard.
 */
final class RedirectToLatestVersionDashboardController extends AbstractController
{
	/**
	 * Execute the controller.
	 *
	 * @return  string
	 */
	public function execute(): bool
	{
		switch ($this->getInput()->getString('software'))
		{
			case 'cms':
				$this->getApplication()->setResponse(
					new RedirectResponse($this->getApplication()->get('uri.base.path') . 'cms/3.x')
				);

				break;

			case 'framework':
				$this->getApplication()->setResponse(
					new RedirectResponse($this->getApplication()->get('uri.base.path') . 'framework/1.x')
				);

				break;

			default:
				throw new RouteNotFoundException(sprintf('Unknown software "%s".', $this->getInput()->getString('software')));
		}

		return true;
	}
}
