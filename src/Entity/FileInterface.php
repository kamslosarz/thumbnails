<?php

namespace App\Entity;

interface FileInterface
{
    public function __construct(string $path);

    public function getFilename(): string;

    public function getContents();
}
