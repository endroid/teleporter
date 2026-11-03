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
        private readonly Teleporter $teleporter,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('sourcePath', InputArgument::REQUIRED)
            ->addArgument('targetPath', InputArgument::REQUIRED)
            ->addArgument('parameters', InputArgument::IS_ARRAY);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourcePath = $this->requireString($input->getArgument('sourcePath'));
        $targetPath = $this->requireString($input->getArgument('targetPath'));
        $parameters = $this->requireParameters($input->getArgument('parameters'));

        $selections = [];
        $replaces = [];
        foreach ($parameters as $argument) {
            $parts = explode('=', $argument);
            if (null === ($parts[1] ?? null)) {
                $selections[] = $parts[0];
                continue;
            }
            $replaces[$parts[0]] = $parts[1];
        }

        $this->teleporter->teleport($sourcePath, $targetPath, $selections, $replaces);

        return Command::SUCCESS;
    }

    private function requireString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Paths and parameters must be strings');
        }

        return $value;
    }

    /** @return list<string> */
    private function requireParameters(mixed $value): array
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Parameters must be an array of strings');
        }

        $parameters = [];
        foreach (array_keys($value) as $key) {
            $parameters[] = $this->requireString($value[$key] ?? null);
        }

        return $parameters;
    }
}
