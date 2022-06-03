<?php

namespace App\Command;

use App\Service\CommandInputBuilderInterface;
use App\Service\RecursiveFileLoader;
use App\Service\ThumbnailFactory;
use Exception;
use League\Flysystem\FilesystemException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CreateThumbnails extends Command
{
    const EXIT_SUCCESS = 0;

    protected static $defaultDescription = 'Create thumbnails from files in directory';
    protected static $defaultName = 'thumbnails:create';

    private CommandInputBuilderInterface $commandInputBuilder;
    private RecursiveFileLoader $recursiveFileLoader;
    private ThumbnailFactory $thumbnailFactory;

    public function __construct(
        CommandInputBuilderInterface $commandInputBuilder,
        RecursiveFileLoader $recursiveFileLoader,
        ThumbnailFactory $thumbnailFactory
    ) {
        parent::__construct();

        $commandInputBuilder->setCommand($this);
        $this->commandInputBuilder = $commandInputBuilder;
        $this->recursiveFileLoader = $recursiveFileLoader;
        $this->thumbnailFactory = $thumbnailFactory;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandInput = $this->commandInputBuilder->build($input, $output);

        if (!$commandInput->isValid()) {
            throw new InvalidArgumentException($commandInput->getLastErrorMessage());
        }

        $output->writeln('<fg=green>Input data is valid, starting...</>');
        $filesystemOperator = $commandInput->getFilesystemOperator();

        /** @var SplFileInfo $fileInfo */
        foreach ($this->recursiveFileLoader->getIterator($commandInput->getPath()) as $fileInfo) {
            $output->write(sprintf('<fg=gray>Creating thumbnail from <fg=white>%s</> ... </>', $fileInfo));
            try {
                $thumbnail = $this->thumbnailFactory->getInstance($fileInfo);
                $thumbnail->create();
                $this->writeSuccess($output);
            } catch (Throwable $exception) {
                $this->writeFailed($output, $exception->getMessage());
                continue;
            }

            $output->write(sprintf('<fg=gray>Saving thumbnail as <fg=white>%s</> ... </>', $thumbnail->getFilename()));
            try {
                $filesystemOperator->write($thumbnail->getFilename(), $thumbnail->getContents());
                $this->writeSuccess($output);
            } catch (Exception|FilesystemException $filesystemException) {
                $this->writeFailed($output, $filesystemException->getMessage());
            }
        }

        return self::EXIT_SUCCESS;
    }

    private function writeSuccess(OutputInterface $output): void
    {
        $output->writeln('<fg=green>Success</>');
    }

    private function writeFailed(OutputInterface $output, string $message): void
    {
        $output->writeln(sprintf('<fg=red>Failed. %s</>', $message));
    }
}

