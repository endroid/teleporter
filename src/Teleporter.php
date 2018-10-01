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
            dump($file);
            die;
        }
    }
}