<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Repository\ClassAliasRepository;
use Joomla\ApiDocumentation\Repository\ClassMethodRepository;
use Joomla\ApiDocumentation\Repository\ClassPropertyRepository;
use Joomla\ApiDocumentation\Repository\ClassRepository;
use Joomla\ApiDocumentation\Repository\FunctionRepository;
use Joomla\ApiDocumentation\Repository\InterfaceMethodRepository;
use Joomla\ApiDocumentation\Repository\InterfaceRepository;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Model repository service provider
 */
final class RepositoryProvider implements ServiceProviderInterface
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
		$container->share(ClassAliasRepository::class, [$this, 'getClassAliasRepositoryClassService']);
		$container->share(ClassMethodRepository::class, [$this, 'getClassMethodRepositoryClassService']);
		$container->share(ClassPropertyRepository::class, [$this, 'getClassPropertyRepositoryClassService']);
		$container->share(ClassRepository::class, [$this, 'getClassRepositoryClassService']);
		$container->share(FunctionRepository::class, [$this, 'getFunctionRepositoryClassService']);
		$container->share(InterfaceMethodRepository::class, [$this, 'getInterfaceMethodRepositoryClassService']);
		$container->share(InterfaceRepository::class, [$this, 'getInterfaceRepositoryClassService']);
	}

	/**
	 * Get the ClassAlias model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassAliasRepository
	 */
	public function getClassAliasRepositoryClassService(Container $container): ClassAliasRepository
	{
		return new ClassAliasRepository;
	}

	/**
	 * Get the ClassMethod model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassMethodRepository
	 */
	public function getClassMethodRepositoryClassService(Container $container): ClassMethodRepository
	{
		return new ClassMethodRepository;
	}

	/**
	 * Get the ClassProperty model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassPropertyRepository
	 */
	public function getClassPropertyRepositoryClassService(Container $container): ClassPropertyRepository
	{
		return new ClassPropertyRepository;
	}

	/**
	 * Get the PHPClass model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassRepository
	 */
	public function getClassRepositoryClassService(Container $container): ClassRepository
	{
		return new ClassRepository;
	}

	/**
	 * Get the PHPFunction model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FunctionRepository
	 */
	public function getFunctionRepositoryClassService(Container $container): FunctionRepository
	{
		return new FunctionRepository;
	}

	/**
	 * Get the InterfaceMethod model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  InterfaceMethodRepository
	 */
	public function getInterfaceMethodRepositoryClassService(Container $container): InterfaceMethodRepository
	{
		return new InterfaceMethodRepository;
	}

	/**
	 * Get the PHPInterface model repository class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  InterfaceRepository
	 */
	public function getInterfaceRepositoryClassService(Container $container): InterfaceRepository
	{
		return new InterfaceRepository;
	}
}
