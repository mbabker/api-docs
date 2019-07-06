<?php declare(strict_types=1);
/**
 * Joomla! API Documentation
 *
 * @copyright  Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\ApiDocumentation\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model defining a version of software.
 *
 * @property  integer                   $id
 * @property  string                    $software
 * @property  string                    $version
 * @property  string                    $display_name
 * @property  Collection|ClassAlias[]   $aliases
 * @property  Collection|PHPClass[]     $classes
 * @property  Collection|PHPFunction[]  $functions
 */
final class Version extends Model
{
	/**
	 * Identifies this version of sofware as being a CMS release.
	 *
	 * @var  string
	 */
	public const SOFTWARE_CMS = 'cms';

	/**
	 * Identifies this version of sofware as being a Framework release.
	 *
	 * @var  string
	 */
	public const SOFTWARE_FRAMEWORK = 'framework';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var  boolean
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $fillable = [
		'software',
		'version',
	];

	/**
	 * Retrieves the list of supported software.
	 *
	 * @return  string[]
	 */
	public static function getSupportedSoftware(): array
	{
		return [
			self::SOFTWARE_CMS,
			self::SOFTWARE_FRAMEWORK,
		];
	}

	/**
	 * Defines the relationship for a software version to the class aliases it has.
	 *
	 * @return  HasMany
	 */
	public function aliases(): HasMany
	{
		return $this->hasMany(ClassAlias::class);
	}

	/**
	 * Defines the relationship for a software version to the PHP classes it has.
	 *
	 * @return  HasMany
	 */
	public function classes(): HasMany
	{
		return $this->hasMany(PHPClass::class);
	}

	/**
	 * Defines the relationship for a software version to the class methods it has.
	 *
	 * @return  HasManyThrough
	 */
	public function class_methods(): HasManyThrough
	{
		return $this->hasManyThrough(ClassMethod::class, PHPClass::class, 'version_id', 'parent_id');
	}

	/**
	 * Defines the relationship for a software version to the class properties it has.
	 *
	 * @return  HasManyThrough
	 */
	public function class_properties(): HasManyThrough
	{
		return $this->hasManyThrough(ClassProperty::class, PHPClass::class, 'version_id', 'parent_id');
	}

	/**
	 * Defines the relationship for a software version to the PHP functions it has.
	 *
	 * @return  HasMany
	 */
	public function functions(): HasMany
	{
		return $this->hasMany(PHPFunction::class);
	}

	/**
	 * Defines the relationship for a software version to the PHP interfaces it has.
	 *
	 * @return  HasMany
	 */
	public function interfaces(): HasMany
	{
		return $this->hasMany(PHPInterface::class);
	}

	/**
	 * Defines the relationship for a software version to the PHP interface methods it has.
	 *
	 * @return  HasManyThrough
	 */
	public function interface_methods(): HasManyThrough
	{
		return $this->hasManyThrough(InterfaceMethod::class, PHPClass::class, 'version_id', 'parent_id');
	}

	/**
	 * Count the number of deprecated code elements in this version.
	 *
	 * @return  integer
	 */
	public function countDeprecations(): int
	{
		$deprecatedClasses = $this->classes()
			->whereHas('deprecation')
			->count();

		$deprecatedClassMethods = $this->class_methods()
			->whereHas('deprecation')
			->count();

		$deprecatedClassProperties = $this->class_properties()
			->whereHas('deprecation')
			->count();

		$deprecatedFunctions = $this->functions()
			->whereHas('deprecation')
			->count();

		$deprecatedInterfaces = $this->interfaces()
			->whereHas('deprecation')
			->count();

		$deprecatedInterfaceMethods = $this->interface_methods()
			->whereHas('deprecation')
			->count();

		return $deprecatedClasses
			+ $deprecatedClassMethods
			+ $deprecatedClassProperties
			+ $deprecatedFunctions
			+ $deprecatedInterfaces
			+ $deprecatedInterfaceMethods;
	}

	/**
	 * Get the root namespaces for the software version.
	 *
	 * If the version has classes in the global namespace, a two item array will be returned containing the name "global" and the root namespace
	 * for all namespaced classes. If the version does not have classes in the global namespace, a one item array will be returned containing the
	 * root namespace for all classes.
	 *
	 * @return  string[]
	 */
	public function getRootNamespaces(): array
	{
		/*
SELECT MIN(u.Name) as Name, LENGTH(u.Name) as len
FROM users u JOIN
     (SELECT MIN(LENGTH(Name)) as minl, MAX(LENGTH(Name)) as maxl
      FROM users u
     ) uu
     ON LENGTH(u.name) IN (uu.minl, uu.maxl)
GROUP BY LENGTH(u.Name);
		 */

		$namespaces = [];

		$globalNamespaceClasses = $this->classes()
			->whereNull('namespace')
			->count();

		if ($globalNamespaceClasses > 0)
		{
			$namespaces[] = 'global';
		}

		$versionRootNamespaces = $this->getConnection()->select(<<<SQL
SELECT MIN(c.namespace) AS namespace
FROM classes c
JOIN
  (SELECT MIN(LENGTH(namespace)) as min_length FROM classes) cc
  ON LENGTH(c.namespace) IN (cc.min_length)
GROUP BY LENGTH(c.namespace)
SQL
		);

		if (count($versionRootNamespaces) > 0)
		{
			$namespaces[] = $versionRootNamespaces[0]->namespace;
		}

		return $namespaces;
	}
}
