<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Teleporter\Command;

use Endroid\Teleporter\Teleporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TeleportCommand extends Command
{
    protected static $defaultName = 'endroid:teleport';

    private $teleporter;

    public function __construct(Teleporter $teleporter)
    {
        $this->teleporter = $teleporter;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sourcePath', InputArgument::REQUIRED)
            ->addArgument('targetPath', InputArgument::REQUIRED)
            ->addArgument('parameters', InputArgument::IS_ARRAY)
            ->setDescription('Teleports files from one location to another')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        return 0;
    }
}
