<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Controller\SoftwareVersion\ClassListing;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Joomla\ApiDocumentation\Controller\AbstractController;
use Joomla\ApiDocumentation\Model\PHPClass;
use Joomla\ApiDocumentation\Model\PHPFunction;
use Joomla\ApiDocumentation\Repository\VersionRepository;
use Joomla\Application\AbstractWebApplication;
use Joomla\Input\Input;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\RouteNotFoundException;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller class to display the list of classes in a namespace in a software version.
 */
final class NamespaceClassListController extends AbstractController
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

		$namespace = str_replace('/', '\\', $this->getInput()->getPath('namespace'));

		// Alpha sort for display
		/** @var Collection|PHPClass[] $classes */
		$classes = $version->getNamespaceClasses($namespace)->sortBy('shortname');

		/** @var Collection|PHPFunction[] $classes */
		$functions = $version->getNamespaceFunctions($namespace)->sortBy('shortname');

		$childNamespaces = $version->getDirectChildNamespaces($namespace);

		if ($classes->isEmpty() && $functions->isEmpty() && count($childNamespaces) === 0)
		{
			throw new RouteNotFoundException(
				sprintf(
					'Namespace "%s" does not exist in the "%s" software at version "%s".',
					$namespace,
					$this->getInput()->getString('software'),
					$this->getInput()->getString('version')
				)
			);
		}

		$this->getApplication()->setResponse(
			new HtmlResponse(
				$this->renderer->render(
					'software_version/class_list.html.twig',
					[
						'child_namespaces'  => $childNamespaces,
						'classes'           => $classes,
						'current_namespace' => $namespace,
						'functions'         => $functions,
						'version'           => $version,
					]
				)
			)
		);

		return true;
	}
}
