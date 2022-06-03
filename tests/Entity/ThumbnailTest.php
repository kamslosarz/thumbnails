<?php

namespace App\Tests\Entity;

use App\Entity\Thumbnail;
use PHPUnit\Framework\TestCase;

class ThumbnailTest extends TestCase
{
    public function testShouldCreateThumbnailAndGetFilename()
    {
        $thumbnail = new Thumbnail(TESTS_DIR.'/fixtures/picture.png');
        $this->assertEquals('picture.png', $thumbnail->getFilename());
        $thumbnail->create();
        $this->assertEquals('picture_thumbnail.png', $thumbnail->getFilename());
    }
}
