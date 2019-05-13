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
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;

/**
 * HTTP service provider
 */
class HttpProvider implements ServiceProviderInterface
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
		$container->share(Http::class, [$this, 'getHttpService']);

		$container->share(HttpFactory::class, [$this, 'getHttpFactoryService']);
	}

	/**
	 * Get the Http class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Http
	 */
	public function getHttpService(Container $container): Http
	{
		/** @var HttpFactory $factory */
		$factory = $container->get(HttpFactory::class);

		return $factory->getHttp();
	}

	/**
	 * Get the HttpFactory class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  HttpFactory
	 */
	public function getHttpFactoryService(Container $container): HttpFactory
	{
		return new HttpFactory;
	}
}
