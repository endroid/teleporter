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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TeleportCommandTest extends TestCase
{
    public function testExecute()
    {
        $teleporter = new Teleporter();
        $teleportCommand = new TeleportCommand($teleporter);
        $commandTester = new CommandTester($teleportCommand);
        $commandTester->execute([
            'sourcePath' => __DIR__.'/../source',
            'targetPath' => __DIR__.'/../target',
            'selections' => ['include'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertEquals('', $output);

        $contents = (string) file_get_contents(__DIR__.'/../target/file.txt');
        $this->assertEquals('include', $contents);
    }
}
