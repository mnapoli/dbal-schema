<?php
declare(strict_types=1);

use DbalSchema\DbalSchemaCommand;
use DbalSchema\SchemaDefinition;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class DbalSchemaCommandTest extends TestCase
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var AbstractSchemaManager
     */
    private $schemaManager;

    public function setUp()
    {
        $this->db = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
        $this->schemaManager = $this->db->getSchemaManager();
    }

    public function test_update_without_force_flag()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema) {} // no tables
        };

        $this->createRandomTable();
        self::assertNotEmpty($this->schemaManager->listTables(), 'A random table exist');

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->update(false, new NullOutput);

        self::assertNotEmpty($this->schemaManager->listTables(), 'The random table was NOT deleted');
    }

    public function test_update_removes_extra_tables()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema) {} // no tables
        };

        $this->createRandomTable();
        self::assertNotEmpty($this->schemaManager->listTables(), 'A random table exist');

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->update(true, new NullOutput);

        self::assertEmpty($this->schemaManager->listTables(), 'The random table was deleted');
    }

    public function test_update_creates_defined_tables()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema)
            {
                $table = $schema->createTable('test');
                $table->addColumn('id', 'integer');
                $table->addColumn('email', 'string');
                $table->setPrimaryKey(['id']);
            }
        };

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->update(true, new NullOutput);

        $tables = $this->schemaManager->listTables();
        self::assertCount(1, $tables);
        self::assertEquals('test', $tables[0]->getName());
    }

    public function test_purge_empties_tables_and_updates_the_schema()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema)
            {
                $table = $schema->createTable('test');
                $table->addColumn('id', 'integer');
                $table->addColumn('email', 'string');
                $table->setPrimaryKey(['id']);
            }
        };
        $command = new DbalSchemaCommand($this->db, $schema);

        // Create the schema
        $command->update(true, new NullOutput);
        // Insert a row
        $this->db->insert('test', [
            'id' => 123,
            'email' => 'foo@bar.com',
        ]);

        $command->purge(true, new NullOutput);

        self::assertEmpty($this->db->fetchAll('SELECT * FROM test'), 'The table is empty');
    }

    public function test_purge_requires_the_force_flag()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema)
            {
                $table = $schema->createTable('test');
                $table->addColumn('id', 'integer');
                $table->addColumn('email', 'string');
                $table->setPrimaryKey(['id']);
            }
        };
        $command = new DbalSchemaCommand($this->db, $schema);

        // Create the schema
        $command->update(true, new NullOutput);
        // Insert a row
        $this->db->insert('test', [
            'id' => 123,
            'email' => 'foo@bar.com',
        ]);

        $command->purge(false, new NullOutput);

        self::assertCount(1, $this->db->fetchAll('SELECT * FROM test'), 'The table is NOT empty');
    }

    private function createRandomTable()
    {
        $createTable = new Table('foo');
        $createTable->addColumn('id', 'integer');
        $this->schemaManager->createTable($createTable);
    }
}
