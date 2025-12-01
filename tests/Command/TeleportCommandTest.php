<?php

declare(strict_types=1);

namespace Endroid\Teleporter\Command;

use Endroid\Teleporter\Teleporter;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class TeleportCommandTest extends TestCase
{
    #[TestDox('Check if a teleport command can be executed')]
    public function testExecute(): void
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

    #[TestDox('Check if the teleport binary can be executed')]
    public function testBinaryExecute(): void
    {
        $binPath = dirname(__DIR__, 2).'/bin/teleport';
        $sourcePath = __DIR__.'/../source';
        $targetPath = __DIR__.'/../target';

        $command = sprintf(
            '%s %s %s %s %s 2>&1',
            escapeshellarg($binPath),
            escapeshellarg($sourcePath),
            escapeshellarg($targetPath),
            escapeshellarg('module_a'),
            escapeshellarg('search=replace')
        );

        exec($command, $output, $exitCode);

        $this->assertSame(0, $exitCode, 'Binary execution failed: '.implode("\n", $output));
        $this->assertSame([], $output);

        $contents = strval(file_get_contents(__DIR__.'/../target/file.txt'));
        $this->assertStringContainsString('Module A content', $contents);
        $this->assertStringNotContainsString('Module B content', $contents);
        $this->assertStringContainsString('replace', $contents);
        $this->assertStringNotContainsString('not found', $contents);
    }
}
