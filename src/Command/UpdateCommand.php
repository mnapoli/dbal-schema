<?php
declare(strict_types=1);

namespace DbalSchema\Command;

use DbalSchema\DbalSchemaCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class UpdateCommand extends Command
{
    /**
     * @var DbalSchemaCommand
     */
    private $schemaCommand;

    public function __construct(DbalSchemaCommand $schemaCommand)
    {
        $this->schemaCommand = $schemaCommand;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('dbal:schema:update')
            ->addOption('force', 'f', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->schemaCommand->update($input->hasOption('force'), $output);
    }
}
