<?php

namespace App\Tests\Service;

use App\Service\RecursiveFileLoader;
use PHPUnit\Framework\TestCase;

class RecursiveFileLoaderTest extends TestCase
{
    public function testShouldLoadDirectoryFiles()
    {
        $recursiveFileLoader = new RecursiveFileLoader();
        $path = TESTS_DIR.'/fixtures';
        $files = [];

        foreach ($recursiveFileLoader->getIterator($path) as $filename) {
            $files[] = "$filename";
        }

        $this->assertEquals([TESTS_DIR.'/fixtures/picture.png'], $files);
    }
}
