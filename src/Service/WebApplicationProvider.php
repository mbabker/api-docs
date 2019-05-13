<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Controller\HomepageController;
use Joomla\ApiDocumentation\Controller\WrongCmsController;
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
		$container->alias(HomepageController::class, 'controller.homepage')
			->share('controller.homepage', [$this, 'getControllerHomepageService'], true);

		$container->alias(WrongCmsController::class, 'controller.wrong.cms')
			->share('controller.wrong.cms', [$this, 'getControllerWrongCmsService'], true);
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
	 * Get the `controller.homepage` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HomepageController
	 */
	public function getControllerHomepageService(Container $container): HomepageController
	{
		return new HomepageController(
			$container->get(RendererInterface::class),
			$container->get(WebApplication::class),
			$container->get(Input::class)
		);
	}

	/**
	 * Get the `controller.wrong.cms` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WrongCmsController
	 */
	public function getControllerWrongCmsService(Container $container): WrongCmsController
	{
		return new WrongCmsController(
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

		return $router;
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
}
