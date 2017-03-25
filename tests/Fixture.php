<?php
declare(strict_types=1);

use DbalSchema\SchemaDefinition;
use Doctrine\DBAL\Schema\Schema;

class Fixture implements SchemaDefinition
{
    public function define(Schema $schema)
    {
        $table = $schema->createTable('test');
        $table->addColumn('id', 'integer');
        $table->addColumn('email', 'string');
        $table->setPrimaryKey(['id']);
    }
}
