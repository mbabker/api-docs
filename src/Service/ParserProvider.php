<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Service;

use Joomla\ApiDocumentation\Importer\ParsedDataImporter;
use Joomla\ApiDocumentation\Repository\ClassMethodRepository;
use Joomla\ApiDocumentation\Repository\ClassRepository;
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
		$container->share(ParsedDataImporter::class, [$this, 'getParsedDataImporterClassService'], true);
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
			new ClassRepository,
			new ClassMethodRepository
		);
	}
}
