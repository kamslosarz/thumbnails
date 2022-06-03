<?php

namespace App\Service;

use FilesystemIterator;
use Iterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class RecursiveFileLoader
{
    public function getIterator(string $path): Iterator
    {
        return new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    }
}
