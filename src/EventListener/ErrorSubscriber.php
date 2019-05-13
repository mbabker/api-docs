<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\EventListener;

use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationErrorEvent as BaseApplicationErrorEvent;
use Joomla\Application\WebApplication;
use Joomla\Console\ConsoleEvents;
use Joomla\Console\Event\ApplicationErrorEvent as ConsoleApplicationErrorEvent;
use Joomla\Event\SubscriberInterface;
use Joomla\Renderer\RendererInterface;
use Joomla\Router\Exception\MethodNotAllowedException;
use Joomla\Router\Exception\RouteNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Error handling event subscriber
 */
class ErrorSubscriber implements SubscriberInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * Layout renderer
	 *
	 * @var  RendererInterface
	 */
	private $renderer;

	/**
	 * Event subscriber constructor.
	 *
	 * @param   RendererInterface  $renderer  Layout renderer
	 */
	public function __construct(RendererInterface $renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ApplicationEvents::ERROR         => 'handleWebError',
			ConsoleEvents::APPLICATION_ERROR => 'handleConsoleError',
		];
	}

	/**
	 * Handle console application errors.
	 *
	 * @param   ConsoleApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	public function handleConsoleError(ConsoleApplicationErrorEvent $event): void
	{
		$this->logger->error(
			sprintf('Uncaught Throwable of type %s caught.', \get_class($event->getError())),
			['exception' => $event->getError()]
		);

		(new SymfonyStyle($event->getApplication()->getConsoleInput(), $event->getApplication()->getConsoleOutput()))
			->error(sprintf('Uncaught Throwable of type %s caught: %s', \get_class($event->getError()), $event->getError()->getMessage()));
	}

	/**
	 * Handle web application errors.
	 *
	 * @param   BaseApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	public function handleWebError(BaseApplicationErrorEvent $event): void
	{
		/** @var WebApplication $app */
		$app = $event->getApplication();

		switch (true)
		{
			case $event->getError() instanceof MethodNotAllowedException :
				// Log the error for reference
				$this->logger->error(
					sprintf('Route `%s` not supported by method `%s`', $app->get('uri.route'), $app->input->getMethod()),
					['exception' => $event->getError()]
				);

				$this->prepareResponse($event);

				$app->setHeader('Allow', implode(', ', $event->getError()->getAllowedMethods()));

				break;

			case $event->getError() instanceof RouteNotFoundException :
				// Log the error for reference
				$this->logger->error(
					sprintf('Route `%s` not found', $app->get('uri.route')),
					['exception' => $event->getError()]
				);

				$this->prepareResponse($event);

				break;

			default:
				$this->logger->error(
					sprintf('Uncaught Throwable of type %s caught.', \get_class($event->getError())),
					['exception' => $event->getError()]
				);

				$this->prepareResponse($event);

				break;
		}
	}

	/**
	 * Prepare the response for the event
	 *
	 * @param   BaseApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	private function prepareResponse(BaseApplicationErrorEvent $event): void
	{
		/** @var \Joomla\FrameworkWebsite\WebApplication $app */
		$app = $event->getApplication();

		$app->allowCache(false);

		$response = new HtmlResponse(
			$this->renderer->render('exception.html.twig', ['exception' => $event->getError()])
		);

		switch ($event->getError()->getCode())
		{
			case 404 :
				$response = $response->withStatus(404);

				break;

			case 405 :
				$response = $response->withStatus(405);

				break;

			case 500 :
			default  :
				$response = $response->withStatus(500);

				break;
		}

		$app->setResponse($response);
	}
}
