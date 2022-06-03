<?php

namespace App\Entity;

use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class Thumbnail implements FileInterface
{
    const MAX_SIZE = 150;

    const THUMBNAIL_SUFFIX = '_thumbnail';
    const THUMBNAIL_FORMAT = 'png';

    private string $filename;
    private string $path;
    private Imagine $imagine;
    private ImageInterface $thumbnail;

    public function __construct(string $path)
    {
        $this->path = $path;
        $info = pathinfo($path);
        $this->filename = sprintf('%s.%s', $info['filename'], $info['extension'] ?? self::THUMBNAIL_FORMAT);
        $this->imagine = new Imagine();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function create(): void
    {
        list($width, $height) = getimagesize($this->path);

        $ratio = $width / $height;
        $width = self::MAX_SIZE;
        $height = self::MAX_SIZE;

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $this->thumbnail = $this->imagine->open($this->path);
        $this->thumbnail->resize(new Box($width, $height));

        $this->updateFilename();
    }

    private function updateFilename()
    {
        $info = pathinfo($this->filename);
        $this->filename = sprintf('%s%s.%s', $info['filename'], self::THUMBNAIL_SUFFIX,
            $info['extension'] ?? self::THUMBNAIL_FORMAT);
    }

    public function getContents(): ?string
    {
        if ($this->thumbnail instanceof Image) {
            ob_start();
            $this->thumbnail->show(self::THUMBNAIL_FORMAT);

            return (string)ob_get_clean();
        }

        return null;
    }
}
