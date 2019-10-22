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
use Symfony\Component\Yaml\Yaml;

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
            ->setName('endroid:teleport')
            ->addArgument('sourcePath', InputArgument::REQUIRED)
            ->addArgument('targetPath', InputArgument::REQUIRED)
            ->addArgument('selections', InputArgument::IS_ARRAY)
            ->setDescription('Teleports files from one location to another')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcePath = strval($input->getArgument('sourcePath'));
        $targetPath = strval($input->getArgument('targetPath'));
        $selections = (array) $input->getArgument('selections');

        $this->teleporter->teleport($sourcePath, $targetPath, $selections);

        return 0;
    }
}
