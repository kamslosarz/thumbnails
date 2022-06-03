<?php

namespace App\Service;

use App\Entity\Thumbnail;

class ThumbnailFactory
{
    public function getInstance(string $filename): Thumbnail
    {
        return new Thumbnail($filename);
    }
}
