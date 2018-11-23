<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Teleporter;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;

class Teleporter
{
    private $finder;
    private $expressionLanguage;
    private $fileSystem;

    public function __construct()
    {
        $this->finder = new Finder();
        $this->expressionLanguage = new ExpressionLanguage();
        $this->fileSystem = new Filesystem();

        $this->finder->ignoreDotFiles(false);
    }

    public function teleport(string $sourcePath, string $targetPath, iterable $selections): void
    {
        $renderer = $this->createRenderer($sourcePath);
        $context = $this->createRendererContext($selections);

        /** @var SplFileInfo[] $files */
        $files = $this->finder->files()->in($sourcePath);

        foreach ($files as $file) {
            $contents = $renderer->render($file->getRelativePathname(), $context);

            if ('' === trim($contents) && file_get_contents($file->getPathname()) != $contents) {
                continue;
            }

            $this->fileSystem->dumpFile($targetPath.'/'.$file->getRelativePathname(), $contents);
            $this->fileSystem->chmod($targetPath.'/'.$file->getRelativePathname(), $file->getPerms());
        }
    }

    private function createRenderer(string $sourcePath): Environment
    {
        $loader = new FilesystemLoader($sourcePath);
        $twig = new Environment($loader);
        $twig->setLexer(new Lexer($twig, [
            'tag_block' => ['{##', '##}'],
            'tag_comment' => ['{#*#*#', '#*#*#}'],
            'tag_variable' => ['{{*#*#', '#*#*}}'],
            'interpolation' => ['#{*#*#', '#*#*}'],
        ]));

        return $twig;
    }

    private function createRendererContext(iterable $selections): iterable
    {
        $context = [];
        foreach ($selections as $selection) {
            $context[$selection] = true;
        }

        return $context;
    }
}
