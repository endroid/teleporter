<?php

declare(strict_types=1);

namespace Endroid\Teleporter;

use Cocur\Slugify\Slugify;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final readonly class Teleporter
{
    public function __construct(
        private Filesystem $fileSystem = new Filesystem(),
    ) {
    }

    /**
     * @param array<string> $selections
     * @param array<string> $replaces
     */
    public function teleport(string $sourcePath, string $targetPath, array $selections, array $replaces): void
    {
        $skipFolders = $this->determineSkipFolders($sourcePath, $selections);

        $renderer = $this->createRenderer($sourcePath);
        $context = $this->createRendererContext($selections, $replaces);

        $finder = new Finder();
        $finder->ignoreDotFiles(false);

        /** @var SplFileInfo[] $files */
        $files = $finder
            ->files()
            ->notName('.teleport')
            ->notName('.teleport.yaml')
            ->in($sourcePath)
        ;

        foreach ($files as $file) {
            if ($this->isInSkipFolder($file, $skipFolders)) {
                continue;
            }

            if ($this->isBinary($file)) {
                $contents = (string) file_get_contents($file->getPathname());
            } else {
                $contents = $renderer->render($file->getRelativePathname(), $context);
            }

            if ('' === trim($contents) && file_get_contents($file->getPathname()) != $contents) {
                continue;
            }

            $this->fileSystem->dumpFile($targetPath.'/'.$file->getRelativePathname(), $contents);
            $this->fileSystem->chmod($targetPath.'/'.$file->getRelativePathname(), intval($file->getPerms()));
        }
    }

    /**
     * @param array<string> $selections
     *
     * @return array<string>
     */
    private function determineSkipFolders(string $sourcePath, array $selections): array
    {
        $skipFolders = [];

        $finder = new Finder();
        $finder->ignoreDotFiles(false);

        /** @var SplFileInfo[] $files */
        $files = $finder->files()->name('.teleport')->in($sourcePath);

        foreach ($files as $file) {
            $contents = strval(file_get_contents($file->getPathname()));
            $requires = explode("\n", $contents);
            $intersection = array_intersect($selections, $requires);

            if (0 === count($intersection)) {
                $skipFolders[] = $file->getPath();
            }
        }

        return $skipFolders;
    }

    /** @param array<string> $skipFolders */
    private function isInSkipFolder(SplFileInfo $fileInfo, array $skipFolders): bool
    {
        foreach ($skipFolders as $skipFolder) {
            if (str_starts_with($fileInfo->getPath(), $skipFolder)) {
                return true;
            }
        }

        return false;
    }

    private function isBinary(SplFileInfo $fileInfo): bool
    {
        $fileInfoMimeType = finfo_open(FILEINFO_MIME);

        if (false === $fileInfoMimeType) {
            throw new \Exception('Mime type could not be determined');
        }

        $mimeType = finfo_file($fileInfoMimeType, $fileInfo->getPathname());

        if (false === $mimeType) {
            throw new \Exception('Mime type could not be determined');
        }

        if (str_ends_with($mimeType, 'binary')) {
            return true;
        }

        if (!str_starts_with($mimeType, 'text')) {
            return true;
        }

        return false;
    }

    private function createRenderer(string $sourcePath): Environment
    {
        $loader = new FilesystemLoader($sourcePath);
        $twig = new Environment($loader);

        // Add slugify filter
        $twig->addFilter(new TwigFilter('slug', function (string $contents) {
            $slugify = new Slugify();

            return $slugify->slugify($contents);
        }));

        // Make sure regular Twig files are not affected
        $twig->setLexer(new Lexer($twig, [
            'tag_block' => ['{--', '--}'],
            'tag_comment' => ['{----', '----}'],
            'tag_variable' => ['{---', '---}'],
            'interpolation' => ['{-----', '-----}'],
        ]));

        return $twig;
    }

    /**
     * @param array<string> $selections
     * @param array<string> $replaces
     *
     * @return array<mixed>
     */
    private function createRendererContext(array $selections, array $replaces): array
    {
        $context = [];

        foreach ($selections as $selection) {
            $context[$selection] = true;
        }

        foreach ($replaces as $search => $replace) {
            $context[$search] = $replace;
        }

        return $context;
    }
}
