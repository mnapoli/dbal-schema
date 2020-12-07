<?php declare(strict_types=1);

namespace DbalSchema\Test;

use DbalSchema\DbalSchemaProvider;
use DbalSchema\SchemaDefinition;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Provider\SchemaProvider;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    private DependencyFactory $migration;

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
        $file = $this->migration->getDiffGenerator()->generate('DbalSchema\Test\Fixture\V1', null);

        self::assertFileEquals(__DIR__ . '/Expected/V1.php', $file);
    }
}
