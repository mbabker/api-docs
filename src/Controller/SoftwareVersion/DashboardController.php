<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller\SoftwareVersion;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Joomla\ApiDocumentation\Controller\AbstractController;
use Joomla\ApiDocumentation\Repository\VersionRepository;
use Joomla\Application\AbstractWebApplication;
use Joomla\Input\Input;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\RouteNotFoundException;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller class to display a software version's dashboard.
 */
final class DashboardController extends AbstractController
{
	/**
	 * The view renderer.
	 *
	 * @var  RendererInterface
	 */
	private $renderer;

	/**
	 * Version model repository.
	 *
	 * @var  VersionRepository
	 */
	private $versionRepository;

	/**
	 * Instantiate the controller.
	 *
	 * @param   RendererInterface       $renderer           The view renderer.
	 * @param   VersionRepository       $versionRepository  Version model repository.
	 * @param   AbstractWebApplication  $app                The application object.
	 * @param   Input|null              $input              The input object.
	 */
	public function __construct(RendererInterface $renderer, VersionRepository $versionRepository, AbstractWebApplication $app, ?Input $input = null)
	{
		$this->renderer          = $renderer;
		$this->versionRepository = $versionRepository;

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

		try
		{
			$version = $this->versionRepository->findSoftwareVersionOrFail(
				$this->getInput()->getString('software'),
				$this->getInput()->getString('version')
			);
		}
		catch (ModelNotFoundException $exception)
		{
			throw new RouteNotFoundException(
				sprintf(
					'Cannot find entry for "%s" at version "%s".',
					$this->getInput()->getString('software'),
					$this->getInput()->getString('version')
				),
				$exception->getCode(),
				$exception
			);
		}

		$this->getApplication()->setResponse(
			new HtmlResponse(
				$this->renderer->render(
					'software_version/dashboard.html.twig',
					[
						'class_count'       => $version->classes()->count(),
						'deprecation_count' => $version->countDeprecations(),
						'function_count'    => $version->functions()->count(),
						'interface_count'   => $version->interfaces()->count(),
						'namespaces'        => $version->getRootNamespaces(),
						'version'           => $version,
					]
				)
			)
		);

		return true;
	}
}
