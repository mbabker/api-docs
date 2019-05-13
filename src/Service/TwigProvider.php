<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Service\Exception\InvalidConfigurationException;
use Joomla\ApiDocumentation\Twig\CdnExtension;
use Joomla\ApiDocumentation\Twig\PhpExtension;
use Joomla\ApiDocumentation\Twig\RoutingExtension;
use Joomla\ApiDocumentation\Twig\Service\CdnRenderer;
use Joomla\ApiDocumentation\Twig\Service\Router;
use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Http\Http;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Psr\Cache\CacheItemPoolInterface;
use Twig\Cache\CacheInterface;
use Twig\Cache\FilesystemCache;
use Twig\Cache\NullCache;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * Twig service provider
 */
final class TwigProvider implements ServiceProviderInterface
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
		/*
		 * Joomla Renderer integration
		 */

		$container->alias(TwigRenderer::class, RendererInterface::class)
			->share(RendererInterface::class, [$this, 'getRendererService']);

		/*
		 * Twig integration
		 */

		$container->alias(\Twig_CacheInterface::class, CacheInterface::class)
			->share(CacheInterface::class, [$this, 'getTwigCacheService']);

		$container->alias(\Twig_Environment::class, Environment::class)
			->share(Environment::class, [$this, 'getTwigEnvironmentService']);

		$container->alias(\Twig_LoaderInterface::class, LoaderInterface::class)
			->share(LoaderInterface::class, [$this, 'getTwigLoaderService']);

		$container->alias(\Twig_RuntimeLoaderInterface::class, RuntimeLoaderInterface::class)
			->share(RuntimeLoaderInterface::class, [$this, 'getTwigRuntimeLoaderService']);

		/*
		 * Twig extensions (local and upstream)
		 */

		$container->share(CdnExtension::class, [$this, 'getTwigExtensionCdnService']);

		$container->alias(\Twig_Extension_Debug::class, DebugExtension::class)
			->share(DebugExtension::class, [$this, 'getTwigExtensionDebugService']);

		$container->share(PhpExtension::class, [$this, 'getTwigExtensionPhpService']);

		$container->share(RoutingExtension::class, [$this, 'getTwigExtensionRoutingService']);

		/*
		 * Service classes
		 */

		$container->share(CdnRenderer::class, [$this, 'getCdnRendererService']);
		$container->share(Router::class, [$this, 'getRouterService']);

		$this->tagTwigExtensions($container);
	}

	/**
	 * Get the CDN renderer class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CdnRenderer
	 */
	public function getCdnRendererService(Container $container): CdnRenderer
	{
		return new CdnRenderer(
			$container->get(CacheItemPoolInterface::class),
			$container->get(Http::class)
		);
	}

	/**
	 * Get the RendererInterface service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RendererInterface
	 */
	public function getRendererService(Container $container): RendererInterface
	{
		return new TwigRenderer($container->get(Environment::class));
	}

	/**
	 * Get the Router class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Router
	 */
	public function getRouterService(Container $container): Router
	{
		return new Router($container->get(AbstractApplication::class));
	}

	/**
	 * Get the Twig cache service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CacheInterface
	 */
	public function getTwigCacheService(Container $container): CacheInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		// Pull down the renderer config
		$cacheEnabled = $config->get('twig.cache.enabled', false);
		$cachePath    = $config->get('twig.cache.path', dirname(__DIR__, 2) . '/cache/twig');
		$debug        = $config->get('twig.debug', false);

		if ($debug === false && $cacheEnabled !== false)
		{
			return new FilesystemCache($cachePath);
		}

		return new NullCache;
	}

	/**
	 * Get the Twig Environment service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Environment
	 */
	public function getTwigEnvironmentService(Container $container): Environment
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		$debug = $config->get('twig.debug', false);

		$environment = new Environment(
			$container->get(LoaderInterface::class),
			['debug' => $debug]
		);

		// Add the runtime loader
		$environment->addRuntimeLoader($container->get(RuntimeLoaderInterface::class));

		// Set up the environment's caching service
		$environment->setCache($container->get(CacheInterface::class));

		// Add the Twig extensions
		$environment->setExtensions($container->getTagged('twig.extension'));

		return $environment;
	}

	/**
	 * Get the Twig CDN extension class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CdnExtension
	 */
	public function getTwigExtensionCdnService(Container $container): CdnExtension
	{
		return new CdnExtension;
	}

	/**
	 * Get the Twig debug extension class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DebugExtension
	 */
	public function getTwigExtensionDebugService(Container $container): DebugExtension
	{
		return new DebugExtension;
	}

	/**
	 * Get the Twig PHP extension class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PhpExtension
	 */
	public function getTwigExtensionPhpService(Container $container): PhpExtension
	{
		return new PhpExtension;
	}

	/**
	 * Get the Twig routing extension class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RoutingExtension
	 */
	public function getTwigExtensionRoutingService(Container $container): RoutingExtension
	{
		return new RoutingExtension;
	}

	/**
	 * Get the Twig loader service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoaderInterface
	 *
	 * @throws  InvalidConfigurationException
	 */
	public function getTwigLoaderService(Container $container): LoaderInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		$paths = $config->get('twig.paths', [dirname(__DIR__, 2) . '/templates']);

		if (!\is_array($paths))
		{
			throw new InvalidConfigurationException('The "twig.paths" configuration must be an array.');
		}

		return new FilesystemLoader($paths);
	}

	/**
	 * Get the Twig runtime loader service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RuntimeLoaderInterface
	 */
	public function getTwigRuntimeLoaderService(Container $container): RuntimeLoaderInterface
	{
		return new ContainerRuntimeLoader($container);
	}

	/**
	 * Tag services which are Twig extensions
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	private function tagTwigExtensions(Container $container): void
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config.decorated');

		$debug = $config->get('twig.debug', false);

		$twigExtensions = [
			CdnExtension::class,
			PhpExtension::class,
			RoutingExtension::class,
		];

		if ($debug)
		{
			$twigExtensions[] = DebugExtension::class;
		}

		$container->tag('twig.extension', $twigExtensions);
	}
}
