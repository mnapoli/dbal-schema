<?php declare(strict_types=1);

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
    private DbalSchemaCommand $schemaCommand;

    public function __construct(DbalSchemaCommand $schemaCommand)
    {
        $this->schemaCommand = $schemaCommand;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('dbal:schema:update')
            ->addOption('force', 'f', InputOption::VALUE_NONE)
            ->addOption('no-transactions', 't', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->schemaCommand->update(
            $input->getOption('force'),
            $output,
            $input->getOption('no-transactions'),
        );

        return 0;
    }
}
