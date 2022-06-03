<?php

namespace App\Entity;

use League\Flysystem\FilesystemOperator;

class ThumbnailCommandInput
{
    private string $path;
    private ?FilesystemOperator $filesystemOperator;
    private ?string $lastErrorMessage = null;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilesystemOperator(): ?FilesystemOperator
    {
        return $this->filesystemOperator;
    }

    public function setFilesystemOperator(FilesystemOperator $filesystemOperator)
    {
        $this->filesystemOperator = $filesystemOperator;
    }

    public function getLastErrorMessage(): ?string
    {
        return $this->lastErrorMessage;
    }

    public function isValid(): bool
    {
        if (!is_readable($this->path)) {
            $this->lastErrorMessage = sprintf('Path \'%s\' is not invalid or not readable', $this->path);

            return false;
        }

        return true;
    }
}
