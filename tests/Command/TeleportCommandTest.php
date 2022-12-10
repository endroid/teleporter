<?php

declare(strict_types=1);

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
            'parameters' => ['module_a', 'search=replace'],
        ]);

        $output = $commandTester->getDisplay();
        $this->assertEquals('', $output);

        $contents = strval(file_get_contents(__DIR__.'/../target/file.txt'));
        $this->assertStringContainsString('Module A content', $contents);
        $this->assertStringNotContainsString('Module B content', $contents);
        $this->assertStringContainsString('replace', $contents);
        $this->assertStringNotContainsString('not found', $contents);
    }
}
