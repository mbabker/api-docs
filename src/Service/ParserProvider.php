<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Importer\ParsedDataImporter;
use Joomla\ApiDocumentation\Parser\FilesystemParser;
use Joomla\ApiDocumentation\Parser\NodeParser;
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
		$container->share(FilesystemParser::class, [$this, 'getFilesystemParserClassService']);

		/*
		 * Node Parser services
		 */
		$container->share(NodeParser::class, [$this, 'getNodeParserClassService']);
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
	 * Get the filesystem parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FilesystemParser
	 */
	public function getFilesystemParserClassService(Container $container): FilesystemParser
	{
		return new FilesystemParser(
			$container->get(NodeParser::class)
		);
	}

	/**
	 * Get the node parser class service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  NodeParser
	 */
	public function getNodeParserClassService(Container $container): NodeParser
	{
		return new NodeParser;
	}
}
