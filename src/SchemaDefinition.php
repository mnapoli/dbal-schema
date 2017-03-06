<?php
declare(strict_types = 1);

namespace DbalSchema;

use Doctrine\DBAL\Schema\Schema;

interface SchemaDefinition
{
    /**
     * Define a schema by configuring the provided Schema instance.
     */
    public function define(Schema $schema);
}
