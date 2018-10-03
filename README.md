# Joomla! API Documentation

## Requirements

- PHP 7.1+
- PDO with MySQL support
- Composer
- Git

## Local Setup

- Clone this repository
- Create a `repos` directory
- Clone the Joomla! CMS GitHub repository into `repos/cms`, `git clone https://github.com/joomla/joomla-cms.git repos/cms` should do the trick
- Copy `etc/config.yaml.dist` to `etc/config.yaml` and fill in your database credentials
    - Leave the database driver alone, MySQL support only here
- Run `bin/console database:migrate` to set up the database
- Run `bin/console add-software --name="Joomla! CMS" --slug="cms"` to add the "Joomla! CMS" software
- Run `bin/console add-software-version 3.x` to add the 3.x branch to the CMS
- Run `bin/console parse-files cms 3.x` to parse the latest 3.x release's files (defined in `etc/versions.yaml`), this creates a JSON data file in the repo root
- Run `bin/console import-data cms 3.x` to import the JSON data dump you just created into the database
    - You will need to run this twice on the initial setup to ensure all relations get seeded correctly, after that one run will correctly insert/update data
    
## Tools

### Database

The database API in use in this application is Laravel's Eloquent ORM. Because the data model calls for a heavy relational data schema, using `joomla/database` on its own would be rather complex to manage and the `joomla/entities` GSoC project at the time this project started did not efficiently support the complex lookups in use.

Custom commands to utilize Laravel's migrations API have been written (see the `src/Command/Database` directory). Aside from using a custom subclass this app's migrations inherit from and not using facades, most Laravel documentation on migrations should apply here.

### Laravel Bridge

To make everything work efficiently, there is some bridging between the core Joomla! Framework and the Laravel framework. Points of interest:

- Using the container inheritance feature of `joomla/di`, it has the Laravel Container class as a parent class meaning that the services defined in this application can resolve services defined in the Laravel container, but if you are in the scope of the Laravel container you can't resolve services in the Joomla container
    - Realistically everything could be written using one container, most likely `illuminate/container` since the service providers from some of the Laravel components are used as well, but for now I basically used the Joomla container for app specific things and the Laravel container for upstream dependencies
    - If there really is a need to access the Laravel container within context of the Joomla container's configuration (the Kernel or service providers), call `$container->get('illuminate.container')`
- The app configuration `Joomla\Registry\Registry` instance is decorated with a `Joomla\ApiDocumentation\Config\ConfigRegistry` object to bind the `config` service in the Laravel container, this allows for a single configuration source and the dependency for the Joomla application classes to satisfy the `Illuminate\Contracts\Config\Repository` interface
    - Because of this, calls to `$container->get('config')` return the `ConfigRegistry` object as the `config` key is "reserved" in the Laravel container; if the decorated `Registry` is needed then `$container->get('config.decorated')` should be called
