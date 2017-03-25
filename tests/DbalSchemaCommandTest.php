<?php
declare(strict_types=1);

use DbalSchema\DbalSchemaCommand;
use DbalSchema\SchemaDefinition;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
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

    public function setUp()
    {
        $this->db = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
    }

    public function test_db_purge()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema) {} // no tables
        };

        $this->createRandomTable();
        self::assertNotEmpty($this->db->getSchemaManager()->listTables(), 'A random table exist');

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->purge(true, new NullOutput);

        self::assertEmpty($this->db->getSchemaManager()->listTables(), 'The random table was deleted');
    }

    public function test_db_purge_without_force_flag()
    {
        $schema = new class implements SchemaDefinition {
            public function define(Schema $schema) {} // no tables
        };

        $this->createRandomTable();
        self::assertNotEmpty($this->db->getSchemaManager()->listTables(), 'A random table exist');

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->purge(false, new NullOutput);

        self::assertNotEmpty($this->db->getSchemaManager()->listTables(), 'The random table was NOT deleted');
    }

    public function test_schema_update()
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

        $this->createRandomTable();
        self::assertNotEmpty($this->db->getSchemaManager()->listTables(), 'A random table exist');

        $command = new DbalSchemaCommand($this->db, $schema);
        $command->purge(true, new NullOutput);

        $tables = $this->db->getSchemaManager()->listTables();
        self::assertCount(1, $tables);
        self::assertEquals('test', $tables[0]->getName());
    }

    private function createRandomTable()
    {
        $createTable = new Table('foo');
        $createTable->addColumn('id', 'integer');
        $this->db->getSchemaManager()->createTable($createTable);
    }
}
