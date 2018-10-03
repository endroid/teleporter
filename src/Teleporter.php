<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Teleporter;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Teleporter
{
    private $finder;
    private $fileSystem;

    public function __construct()
    {
        $this->finder = new Finder();
        $this->finder->ignoreDotFiles(false);
    }

    public function teleport(string $sourcePath, string $targetPath, array $conditions = []): void
    {
        $files = $this->finder->files()->in($sourcePath);

        foreach ($files as $file) {
            $this->teleportFile($file, $targetPath, $conditions);
        }
    }

    private function teleportFile(SplFileInfo $fileInfo, string $targetPath, array $conditions): void
    {
        $contents = $fileInfo->getContents();
        $contents = $this->filterContents($contents, $conditions);
    }

    private function filterContents(string $contents, array $conditions): string
    {
        $parts = preg_split('/[ \t]*### (.+) ###\n?/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);

    }
}