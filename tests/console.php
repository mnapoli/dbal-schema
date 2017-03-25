<?php

use DbalSchema\DbalSchemaCommand;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Output\NullOutput;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Fixture.php';

$schema = new Fixture;
$connection = DriverManager::getConnection([
    'url' => 'sqlite:///:memory:',
]);

$command = new DbalSchemaCommand($connection, $schema);
$command->update(false, new NullOutput);
