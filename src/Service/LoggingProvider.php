<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

/**
 * Logging service provider
 */
class LoggingProvider implements ServiceProviderInterface
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
		/*
		 * Monolog Handlers
		 */
		$container->share('monolog.handler.stream', [$this, 'getMonologHandlerStreamService']);

		/*
		 * Monolog Processors
		 */
		$container->share('monolog.processor.psr3', [$this, 'getMonologProcessorPsr3Service']);

		/*
		 * Application Loggers
		 */
		$container->alias(LoggerInterface::class, 'monolog.logger')
			->alias(Logger::class, 'monolog.logger')
			->share('monolog.logger', [$this, 'getMonologLoggerService']);
	}

	/**
	 * Get the `monolog.handler.stream` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 */
	public function getMonologHandlerStreamService(Container $container): StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		$level = strtoupper($config->get('log.level', 'error'));

		return new StreamHandler(dirname(__DIR__, 2) . '/logs/app.log', \constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.logger.app` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 */
	public function getMonologLoggerService(Container $container): Logger
	{
		return new Logger(
			'ApiDocs',
			[
				$container->get('monolog.handler.stream'),
			],
			[
				$container->get('monolog.processor.psr3'),
			]
		);
	}

	/**
	 * Get the `monolog.processor.psr3` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PsrLogMessageProcessor
	 */
	public function getMonologProcessorPsr3Service(Container $container): PsrLogMessageProcessor
	{
		return new PsrLogMessageProcessor;
	}
}
