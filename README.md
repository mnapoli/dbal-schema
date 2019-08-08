# DBAL Schema

DB schema manager for [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html).

## Why?

Doctrine ORM can automatically manage your DB schema based on your entity mapping. This feature is lost when using the DBAL instead of the ORM.

This package lets you achieve something similar by defining your DB schema with PHP code. It also lets you manage your database using a Symfony Console command similar to Symfony's native `doctrine:schema:update` command.

## Installation

```bash
$ composer require mnapoli/dbal-schema
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

#### Using Symfony

Here is an example of configuration that can go in your `config.yml`:

```yaml
services:

    DbalSchema\SchemaDefinition:
        # Replace this with your class name
        alias: App\Database\MySchemaDefinition
    # Register the commands:
    DbalSchema\DbalSchemaCommand:
    DbalSchema\Command\UpdateCommand:
    DbalSchema\Command\PurgeCommand:
```

This configuration assumes your services are autowired.

Once the services are registered, you can now run the commands:

```bash
bin/console dbal:schema:update
bin/console dbal:schema:purge
```

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
