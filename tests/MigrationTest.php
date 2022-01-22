<?php declare(strict_types=1);

namespace DbalSchema\Test;

use DbalSchema\DbalSchemaProvider;
use DbalSchema\SchemaDefinition;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Provider\SchemaProvider;
use Doctrine\Migrations\Version\Version;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    private DependencyFactory $migration;
    private const MIGRATION_NAME = 'DbalSchema\Test\Fixture\Version1';

    public function setUp(): void
    {
        $db = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
        $configuration = new ConfigurationArray([
            'migrations_paths' => [
                'DbalSchema\Test\Fixture' => __DIR__ . '/Fixture',
            ],
        ]);
        $this->migration = DependencyFactory::fromConnection($configuration, new ExistingConnection($db));
    }

    public function test generates migration from schema(): void
    {
        // 1: define a schema
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema): void
            {
                $table = $schema->createTable('test');
                $table->addColumn('id', 'integer');
                $table->addColumn('email', 'string');
                $table->setPrimaryKey(['id']);
            }
        };
        $this->migration->setService(SchemaProvider::class, new DbalSchemaProvider($schema));

        // 2: generate the migration
        $this->migration->getDiffGenerator()->generate(self::MIGRATION_NAME, null);

        // 3. assert
        $migration = $this->getMigration();
        $migration->up(new Schema());
        $this->assertEquals(
            'CREATE TABLE test (id INTEGER NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))',
            $migration->getSql()[0]->getStatement()
        );

        $migration = $this->getMigration();
        $migration->down(new Schema());
        $this->assertEquals(
            'DROP TABLE test',
            $migration->getSql()[0]->getStatement()
        );
    }

    private function getMigration(): AbstractMigration
    {
        // clone is needed to reset state between the up() and down() assertions
        return clone $this->migration->getMigrationRepository()->getMigration(
            new Version(self::MIGRATION_NAME)
        )->getMigration();
    }
}
