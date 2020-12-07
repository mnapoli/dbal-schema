<?php declare(strict_types=1);

namespace DbalSchema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Provider\SchemaProvider;

class DbalSchemaProvider implements SchemaProvider
{
    private SchemaDefinition $schemaDefinition;

    public function __construct(SchemaDefinition $schemaDefinition)
    {
        $this->schemaDefinition = $schemaDefinition;
    }

    public function createSchema(): Schema
    {
        $schema = new Schema();
        $this->schemaDefinition->define($schema);

        return $schema;
    }
}
