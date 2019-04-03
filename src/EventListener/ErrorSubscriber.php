<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\EventListener;

use Joomla\Console\ConsoleEvents;
use Joomla\Console\Event\ApplicationErrorEvent;
use Joomla\Event\SubscriberInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Error handling event subscriber
 */
class ErrorSubscriber implements SubscriberInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ConsoleEvents::APPLICATION_ERROR => 'handleConsoleError',
		];
	}

	/**
	 * Handle console application errors.
	 *
	 * @param   ApplicationErrorEvent  $event  Event object
	 *
	 * @return  void
	 */
	public function handleConsoleError(ApplicationErrorEvent $event): void
	{
		$this->logger->error(
			sprintf('Uncaught Throwable of type %s caught.', \get_class($event->getError())),
			['exception' => $event->getError()]
		);

		(new SymfonyStyle($event->getApplication()->getConsoleInput(), $event->getApplication()->getConsoleOutput()))
			->error(sprintf('Uncaught Throwable of type %s caught: %s', \get_class($event->getError()), $event->getError()->getMessage()));
	}
}
