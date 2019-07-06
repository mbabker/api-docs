<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller;

use Joomla\Application\AbstractWebApplication;
use Joomla\Controller\ControllerInterface;
use Joomla\Input\Input;

/**
 * Base controller class for the API documentation application
 */
abstract class AbstractController implements ControllerInterface
{
	/**
	 * The application object.
	 *
	 * @var  AbstractWebApplication
	 */
	private $app;

	/**
	 * The input object.
	 *
	 * @var  Input
	 */
	private $input;

	/**
	 * Instantiate the controller.
	 *
	 * @param   AbstractWebApplication  $app    The application object.
	 * @param   Input|null              $input  The input object.
	 */
	public function __construct(AbstractWebApplication $app, ?Input $input = null)
	{
		$this->app   = $app;
		$this->input = $input ?: $app->input;
	}

	/**
	 * Get the application object.
	 *
	 * @return  AbstractWebApplication
	 */
	protected function getApplication(): AbstractWebApplication
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return  Input
	 */
	protected function getInput(): Input
	{
		return $this->input;
	}
}
