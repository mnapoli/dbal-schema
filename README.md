# DBAL Schema

DB schema manager for [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html).

## Why?

Doctrine ORM can automatically manage your DB schema based on your entity mapping. This feature is lost when using the DBAL instead of the ORM.

This package lets you achieve something similar by defining your DB schema with PHP code. It also lets you manage your database using a Symfony Console command similar to Symfony's native `doctrine:schema:update` command.

## Installation

```
composer require mnapoli/dbal-schema
```

## Usage

### 1. Define a schema

Define your DB schema by implementing the `SchemaDefinition` interface:

```php
class MySchemaDefinition implements SchemaDefinition
{
    public function define(Schema $schema)
    {
        $usersTable = $schema->createTable('users');
        $usersTable->addColumn('id', 'integer');
        $usersTable->addColumn('email', 'string');
        $usersTable->addColumn('lastLogin', 'datetime');
        $usersTable->addColumn('score', 'float');
        $usersTable->setPrimaryKey(['id']);
        $usersTable->addUniqueIndex(['email']);
    }
}
```

You can read the whole API available on [Doctrine's documentation](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/schema-representation.html).

### 2. Setup the schema

You can now let Doctrine update your database to match the schema you defined.

#### Using Symfony Console

You need to configure:

- your `MySchemaDefinition` implementation
- the console commands (classes defined in the `DbalSchema\Command\*` namespace)

Here is an example of configuration that can go in your `config.yml`:

```yaml
services:
    dbal_schema.definition:
        # Replace with your actual class:
        class: AppBundle\MySchemaDefinition
    # The config below configures the console commands
    dbal_schema.base_command:
        class: DbalSchema\DbalSchemaCommand
        arguments:
            - '@database_connection'
            - '@dbal_schema.definition'
    dbal_schema.command.update_command:
        class: DbalSchema\Command\UpdateCommand
        arguments: ["@dbal_schema.base_command"]
        tags:
            - { name: console.command }
    dbal_schema.command.purge_command:
        class: DbalSchema\Command\PurgeCommand
        arguments: ["@dbal_schema.base_command"]
        tags:
            - { name: console.command }
```

A pull request to add a proper Symfony bundle would be welcome.

#### Using [Silly](https://github.com/mnapoli/silly)

Using Silly you can ignore the many separate command classes and simply use the `DbalSchemaCommand` class:

```php
$schema = new MySchemaDefinition();
$dbalConnection = /* your DBAL connection, see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html */

$command = new DbalSchemaCommand($dbalConnection, $schema);

$application = new Silly\Application();
$application->command('db [--force]', [$command, 'update']);
$application->command('db-purge [--force]', [$command, 'purge']);
$application->run();
```

If you are using the [Silly PHP-DI edition](https://github.com/mnapoli/silly/blob/master/docs/php-di.md) it's even simpler as [PHP-DI](http://php-di.org/) can instantiate the `DbalSchemaCommand` service:

```php
$application->command('db [--force]', [DbalSchemaCommand::class, 'update']);
$application->command('db-purge [--force]', [DbalSchemaCommand::class, 'purge']);
```
