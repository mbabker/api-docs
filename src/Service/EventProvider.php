<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\EventListener\ErrorSubscriber;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Dispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\Renderer\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * Event service provider
 */
final class EventProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container): void
	{
		$container->alias(Dispatcher::class, DispatcherInterface::class)
			->share(DispatcherInterface::class, [$this, 'getDispatcherService']);

		$container->share(ErrorSubscriber::class, [$this, 'getErrorSubscriber'])
			->tag('event.subscriber', [ErrorSubscriber::class]);
	}

	/**
	 * Get the DispatcherInterface service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DispatcherInterface
	 */
	public function getDispatcherService(Container $container): DispatcherInterface
	{
		$dispatcher = new Dispatcher;

		foreach ($container->getTagged('event.subscriber') as $subscriber)
		{
			$dispatcher->addSubscriber($subscriber);
		}

		return $dispatcher;
	}

	/**
	 * Get the ErrorSubscriber service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ErrorSubscriber
	 */
	public function getErrorSubscriber(Container $container): ErrorSubscriber
	{
		$subscriber = new ErrorSubscriber(
			$container->get(RendererInterface::class)
		);

		$subscriber->setLogger($container->get(LoggerInterface::class));

		return $subscriber;
	}
}
