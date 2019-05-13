<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller;

use Joomla\Application\AbstractWebApplication;
use Joomla\Input\Input;
use Joomla\Renderer\RendererInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller class to display the site homepage.
 */
class HomepageController extends AbstractController
{
	/**
	 * The view renderer.
	 *
	 * @var  RendererInterface
	 */
	private $renderer;

	/**
	 * Instantiate the controller.
	 *
	 * @param   RendererInterface       $renderer  The view renderer.
	 * @param   AbstractWebApplication  $app       The application object.
	 * @param   Input|null              $input     The input object.
	 */
	public function __construct(RendererInterface $renderer, AbstractWebApplication $app, ?Input $input = null)
	{
		$this->renderer = $renderer;

		parent::__construct($app, $input);
	}

	/**
	 * Execute the controller.
	 *
	 * @return  string
	 */
	public function execute(): bool
	{
		// Enable browser caching
		$this->getApplication()->allowCache(true);

		$this->getApplication()->setResponse(new HtmlResponse($this->renderer->render('homepage.html.twig')));

		return true;
	}
}
