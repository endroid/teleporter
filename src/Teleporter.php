<?php

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

    public function teleport(string $sourcePath, string $targetPath, array $conditions = []): void
    {
        /** @var SplFileInfo[] $files */
        $files = $this->finder->files()->in($sourcePath);

        foreach ($files as $file) {
            $this->teleportFile($file, $targetPath.'/'.$file->getRelativePathname(), $file->getPerms(), $conditions);
        }
    }

    private function teleportFile(SplFileInfo $file, string $targetPath, int $permissions, array $conditions): void
    {
        $contents = $file->getContents();
        $contents = $this->filterContents($contents, $conditions);

        if (trim($contents) !== '') {
            $this->fileSystem->dumpFile($targetPath, $contents);
            $this->fileSystem->chmod($targetPath, $permissions);
        }
    }

    private function filterContents(string $contents, array $conditions): string
    {
        $parts = preg_split('/[ \t]*### (.+) ###\n?/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);

        $filteredContents = $parts[0];

        $depth = 0;
        $skipDepth = 1000;

        for ($offset = 1; $offset < count($parts); $offset += 2) {

            $condition = $parts[$offset];
            $condition == 'end' ? $depth-- : $depth++;

            if ($depth >= $skipDepth) {
                continue;
            }

            $condition = str_replace(array_keys($conditionReplaces), array_values($conditionReplaces), $condition);
            $include = $this->expressionLanguage->evaluate($condition);

            if ($include) {
                $filteredContents .= $parts[$offset + 1];
                $skipDepth = 1000;
            } else {
                $skipDepth = $depth;
            }
        }

        return implode('', $parts);
    }
}