<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller;

use Zend\Diactoros\Response\TextResponse;

/**
 * Controller class to display a message to individuals looking for the wrong CMS.
 */
class WrongCmsController extends AbstractController
{
	/**
	 * Execute the controller.
	 *
	 * @return  string
	 */
	public function execute(): bool
	{
		// Enable browser caching
		$this->getApplication()->allowCache(true);

		$response = new TextResponse("This isn't the CMS you're looking for.", 404);

		$this->getApplication()->setResponse($response);

		return true;
	}
}
