<?php

declare(strict_types=1);

namespace Endroid\Teleporter\Command;

use Endroid\Teleporter\Teleporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'endroid:teleport', description: 'Teleports files from one location to another')]
final class TeleportCommand extends Command
{
    public function __construct(
        private Teleporter $teleporter
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sourcePath', InputArgument::REQUIRED)
            ->addArgument('targetPath', InputArgument::REQUIRED)
            ->addArgument('parameters', InputArgument::IS_ARRAY)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourcePath = $input->getArgument('sourcePath');
        $targetPath = $input->getArgument('targetPath');
        $parameters = $input->getArgument('parameters');

        if (!is_string($sourcePath) || !is_string($targetPath) || !is_array($parameters)) {
            throw new \Exception('Please provide a sourcePath, targetPath and parameters (selections and assignments)');
        }

        $selections = [];
        $replaces = [];
        foreach ($parameters as $argument) {
            $parts = explode('=', $argument);
            if (1 === count($parts)) {
                $selections[] = $parts[0];
            } else {
                $replaces[$parts[0]] = $parts[1];
            }
        }

        $this->teleporter->teleport($sourcePath, $targetPath, $selections, $replaces);

        return Command::SUCCESS;
    }
}
