<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Controller\HomepageController;
use Joomla\ApiDocumentation\Controller\SoftwareVersion\DashboardController;
use Joomla\ApiDocumentation\Controller\SoftwareVersion\RedirectToLatestVersionDashboardController;
use Joomla\ApiDocumentation\Controller\WrongCmsController;
use Joomla\ApiDocumentation\Model\Version;
use Joomla\ApiDocumentation\Repository\VersionRepository;
use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Controller\ContainerControllerResolver;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Application\WebApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Route;
use Joomla\Router\Router;
use Psr\Log\LoggerInterface;

/**
 * Web application service provider
 */
final class WebApplicationProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->alias(AbstractWebApplication::class, WebApplication::class)
			->share(WebApplication::class, [$this, 'getWebApplicationClassService']);

		/*
		 * Application Class Dependencies
		 */

		$container->share(Input::class, [$this, 'getInputService']);
		$container->share(Router::class, [$this, 'getRouterService']);

		$container->alias(ContainerControllerResolver::class, ControllerResolverInterface::class)
			->share(ControllerResolverInterface::class, [$this, 'getControllerResolverService']);

		$container->share(WebClient::class, [$this, 'getWebClientService']);

		/*
		 * MVC Layer
		 */

		// Controllers
		$container->share(HomepageController::class, [$this, 'getHomepageControllerService']);
		$container->share(DashboardController::class, [$this, 'getSoftwareVersionDashboardControllerService']);
		$container->share(RedirectToLatestVersionDashboardController::class, [$this, 'getRedirectToLatestVersionDashboardControllerService']);
		$container->share(WrongCmsController::class, [$this, 'getWrongCmsControllerService']);
	}

	/**
	 * Get the controller resolver service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerResolverInterface
	 */
	public function getControllerResolverService(Container $container): ControllerResolverInterface
	{
		return new ContainerControllerResolver($container);
	}

	/**
	 * Get the HomepageController class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HomepageController
	 */
	public function getHomepageControllerService(Container $container): HomepageController
	{
		return new HomepageController(
			$container->get(RendererInterface::class),
			$container->get(WebApplication::class),
			$container->get(Input::class)
		);
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 */
	public function getInputService(Container $container): Input
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the RedirectToLatestVersionDashboardController class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RedirectToLatestVersionDashboardController
	 */
	public function getRedirectToLatestVersionDashboardControllerService(Container $container): RedirectToLatestVersionDashboardController
	{
		return new RedirectToLatestVersionDashboardController(
			$container->get(WebApplication::class),
			$container->get(Input::class)
		);
	}

	/**
	 * Get the router service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Router
	 */
	public function getRouterService(Container $container): Router
	{
		$router = new Router;

		/*
		 * CMS Admin Panels
		 */
		$router->get(
			'/administrator',
			WrongCmsController::class
		);

		$router->get(
			'/administrator/*',
			WrongCmsController::class
		);

		$router->get(
			'/wp-admin',
			WrongCmsController::class
		);

		$router->get(
			'/wp-admin/*',
			WrongCmsController::class
		);

		$router->get(
			'wp-login.php',
			WrongCmsController::class
		);

		/*
		 * Web routes
		 */
		$router->addRoute(new Route(['GET', 'HEAD'], '/', HomepageController::class));

		$router->get(
			'/:software',
			RedirectToLatestVersionDashboardController::class,
			[
				'software' => implode('|', Version::getSupportedSoftware()),
			]
		);

		$router->get(
			'/:software/:version',
			DashboardController::class,
			[
				'software' => implode('|', Version::getSupportedSoftware()),
			]
		);

		return $router;
	}

	/**
	 * Get the DashboardController class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DashboardController
	 */
	public function getSoftwareVersionDashboardControllerService(Container $container): DashboardController
	{
		return new DashboardController(
			$container->get(RendererInterface::class),
			$container->get(VersionRepository::class),
			$container->get(WebApplication::class),
			$container->get(Input::class)
		);
	}

	/**
	 * Get the web application service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebApplication
	 */
	public function getWebApplicationClassService(Container $container): WebApplication
	{
		$application = new WebApplication(
			$container->get(ControllerResolverInterface::class),
			$container->get(Router::class),
			$container->get(Input::class),
			$container->get('config.decorated'),
			$container->get(WebClient::class)
		);

		$application->setDispatcher($container->get(DispatcherInterface::class));
		$application->setLogger($container->get(LoggerInterface::class));

		return $application;
	}

	/**
	 * Get the web client service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebClient
	 */
	public function getWebClientService(Container $container): WebClient
	{
		/** @var Input $input */
		$input          = $container->get(Input::class);
		$userAgent      = $input->server->getString('HTTP_USER_AGENT', '');
		$acceptEncoding = $input->server->getString('HTTP_ACCEPT_ENCODING', '');
		$acceptLanguage = $input->server->getString('HTTP_ACCEPT_LANGUAGE', '');

		return new WebClient($userAgent, $acceptEncoding, $acceptLanguage);
	}

	/**
	 * Get the WrongCmsController class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WrongCmsController
	 */
	public function getWrongCmsControllerService(Container $container): WrongCmsController
	{
		return new WrongCmsController(
			$container->get(WebApplication::class),
			$container->get(Input::class)
		);
	}
}
