<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Importer\ParsedDataImporter;
use Joomla\ApiDocumentation\Parser\File\ArgumentParser;
use Joomla\ApiDocumentation\Parser\File\ClassParser;
use Joomla\ApiDocumentation\Parser\File\ConstantParser;
use Joomla\ApiDocumentation\Parser\File\DocBlockParser;
use Joomla\ApiDocumentation\Parser\File\InterfaceParser;
use Joomla\ApiDocumentation\Parser\Filesystem\ClassmapParser;
use Joomla\ApiDocumentation\Parser\Filesystem\DirectoryParser;
use Joomla\ApiDocumentation\Parser\Filesystem\FileParser;
use Joomla\ApiDocumentation\Repository\ClassMethodRepository;
use Joomla\ApiDocumentation\Repository\ClassRepository;
use Joomla\ApiDocumentation\Repository\FunctionRepository;
use Joomla\ApiDocumentation\Repository\InterfaceMethodRepository;
use Joomla\ApiDocumentation\Repository\InterfaceRepository;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Data parser service provider
 */
final class ParserProvider implements ServiceProviderInterface
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
		 * Importer services
		 */
		$container->share(ParsedDataImporter::class, [$this, 'getParsedDataImporterClassService']);

		/*
		 * Filesystem Parser services
		 */
		$container->share(ClassmapParser::class, [$this, 'getClassmapParserClassService']);
		$container->share(DirectoryParser::class, [$this, 'getDirectoryParserClassService']);
		$container->share(FileParser::class, [$this, 'getFileParserClassService']);

		/*
		 * Node Parser services
		 */
		$container->share(ArgumentParser::class, [$this, 'getArgumentParserClassService']);
		$container->share(ClassParser::class, [$this, 'getClassParserClassService']);
		$container->share(ConstantParser::class, [$this, 'getConstantParserClassService']);
		$container->share(DocBlockParser::class, [$this, 'getDocBlockParserClassService']);
		$container->share(InterfaceParser::class, [$this, 'getInterfaceParserClassService']);
	}

	/**
	 * Get the parsed data importer class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ParsedDataImporter
	 */
	public function getParsedDataImporterClassService(Container $container): ParsedDataImporter
	{
		return new ParsedDataImporter(
			$container->get(ClassRepository::class),
			$container->get(ClassMethodRepository::class),
			$container->get(FunctionRepository::class),
			$container->get(InterfaceRepository::class),
			$container->get(InterfaceMethodRepository::class)
		);
	}

	/**
	 * Get the classmap parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassmapParser
	 */
	public function getClassmapParserClassService(Container $container): ClassmapParser
	{
		return new ClassmapParser;
	}

	/**
	 * Get the directory parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DirectoryParser
	 */
	public function getDirectoryParserClassService(Container $container): DirectoryParser
	{
		return new DirectoryParser(
			$container->get(FileParser::class)
		);
	}

	/**
	 * Get the file parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FileParser
	 */
	public function getFileParserClassService(Container $container): FileParser
	{
		return new FileParser(
			$container->get(ClassParser::class),
			$container->get(InterfaceParser::class),
			$container->get(ArgumentParser::class),
			$container->get(ConstantParser::class),
			$container->get(DocBlockParser::class)
		);
	}

	/**
	 * Get the argument parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ArgumentParser
	 */
	public function getArgumentParserClassService(Container $container): ArgumentParser
	{
		return new ArgumentParser;
	}

	/**
	 * Get the class parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ClassParser
	 */
	public function getClassParserClassService(Container $container): ClassParser
	{
		return new ClassParser(
			$container->get(ArgumentParser::class),
			$container->get(ConstantParser::class),
			$container->get(DocBlockParser::class)
		);
	}

	/**
	 * Get the constant parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConstantParser
	 */
	public function getConstantParserClassService(Container $container): ConstantParser
	{
		return new ConstantParser(
			$container->get(DocBlockParser::class)
		);
	}

	/**
	 * Get the doc block parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DocBlockParser
	 */
	public function getDocBlockParserClassService(Container $container): DocBlockParser
	{
		return new DocBlockParser;
	}

	/**
	 * Get the interface parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  InterfaceParser
	 */
	public function getInterfaceParserClassService(Container $container): InterfaceParser
	{
		return new InterfaceParser(
			$container->get(ArgumentParser::class),
			$container->get(ConstantParser::class),
			$container->get(DocBlockParser::class)
		);
	}
}
