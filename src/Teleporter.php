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
use Symfony\Contracts\Tests\Service\ServiceLocatorTest;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;

class Teleporter
{
    private $expressionLanguage;
    private $fileSystem;
    private $skipFolders;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->fileSystem = new Filesystem();
        $this->skipFolders = [];
    }

    public function teleport(string $sourcePath, string $targetPath, array $selections): void
    {
        $this->determineSkipFolders($sourcePath, $selections);
        
        $renderer = $this->createRenderer($sourcePath);
        $context = $this->createRendererContext($selections);

        $finder = new Finder();
        $finder->ignoreDotFiles(false);

        /** @var SplFileInfo[] $files */
        $files = $finder->files()->notName('.build')->in($sourcePath);

        foreach ($files as $file) {

            if ($this->isInSkipFolder($file)) {
                continue;
            }

            if ($this->isBinary($file)) {
                $contents = file_get_contents($file->getPathname());
            } else {
                $contents = $renderer->render($file->getRelativePathname(), $context);
            }

            if ('' === trim($contents) && file_get_contents($file->getPathname()) != $contents) {
                continue;
            }

            $this->fileSystem->dumpFile($targetPath.'/'.$file->getRelativePathname(), $contents);
            $this->fileSystem->chmod($targetPath.'/'.$file->getRelativePathname(), $file->getPerms());
        }
    }

    private function determineSkipFolders(string $sourcePath, array $selections)
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(false);

        /** @var SplFileInfo[] $files */
        $files = $finder->files()->name('.build')->in($sourcePath);

        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            $requires = explode("\n", $contents);
            $intersection = array_intersect($selections, $requires);

            if (count($intersection) === 0) {
                $this->skipFolders[$file->getPath()] = $file->getPath();
            }
        }
    }

    private function isInSkipFolder(SplFileInfo $fileInfo)
    {
        foreach ($this->skipFolders as $skipFolder) {
            if (strpos($fileInfo->getPath(), $skipFolder) === 0) {
                return true;
            }
        }

        return false;
    }

    private function isBinary(SplFileInfo $fileInfo)
    {
        $fileInfoMimeType = finfo_open(FILEINFO_MIME);
        $mimeType = finfo_file($fileInfoMimeType, $fileInfo->getPathname());

        if (substr($mimeType, -6) === 'binary') {
            return true;
        }

        if (substr($mimeType, 0, 4) !== 'text') {
            return true;
        }

        return false;
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

    private function createRendererContext(array $selections): iterable
    {
        $context = [];
        foreach ($selections as $selection) {
            $context[$selection] = true;
        }

        return $context;
    }
}
