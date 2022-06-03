<?php

namespace App\Service;

use App\Entity\ThumbnailCommandInput;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ThumbnailCommandInputBuilder implements CommandInputBuilderInterface
{
    const STORAGE_LOCAL = 'Local directory';
    const STORAGE_FTP = 'FTP';
    const STORAGE_MAP = [
        0 => self::STORAGE_LOCAL,
        1 => self::STORAGE_FTP,
    ];
    const STORAGE_QUESTION = 'Where to save the thumbnails <info>%s</info>: ';
    const ORIGINAL_DIRECTORY_QUESTION = 'Original images directory <info>%s</info>: ';

    protected ?Command $command = null;
    /** @var ParameterBag $parameterBag */
    private ParameterBagInterface $parameterBag;
    private FilesystemOperator $ftpStorage;
    private FilesystemOperator $localStorage;

    public function __construct(
        ParameterBagInterface $parameterBag,
        FilesystemOperator $ftpStorage,
        FilesystemOperator $localStorage
    ) {
        $this->parameterBag = $parameterBag;
        $this->ftpStorage = $ftpStorage;
        $this->localStorage = $localStorage;
    }

    public function build(InputInterface $input, OutputInterface $output): ThumbnailCommandInput
    {
        $this->configure();

        $defaultPath = $this->getDefaultOriginalImagesPath();
        $path = $this->command->getHelper('path')
            ->ask($input, $output,
                new Question(sprintf(self::ORIGINAL_DIRECTORY_QUESTION, $defaultPath), $defaultPath));

        $defaultStorage = $this->getDefaultStorage();
        $storage = $this->command->getHelper('storage')
            ->ask($input, $output, new ChoiceQuestion(sprintf(self::STORAGE_QUESTION, $defaultStorage), [
                0 => self::STORAGE_LOCAL,
                1 => self::STORAGE_FTP,
            ], $defaultStorage));

        if (!($filesystemOperator = $this->getFilesystemOperator($storage))) {
            throw new InvalidArgumentException(sprintf('Invalid storage "%s"', $storage));
        }

        $thumbnailCommandInput = new ThumbnailCommandInput($path);
        $thumbnailCommandInput->setFilesystemOperator($filesystemOperator);

        return $thumbnailCommandInput;
    }

    private function configure(): void
    {
        if (!$this->command) {
            throw new InvalidArgumentException('Please define command before build');
        }

        $helperSet = new HelperSet();
        $helperSet->set(new QuestionHelper(), 'path');
        $helperSet->set(new QuestionHelper(), 'storage');

        $this->command->setHelperSet($helperSet);
    }

    /**
     * @return string
     */
    private function getDefaultOriginalImagesPath(): string
    {
        return (string)$this->parameterBag->get('default_original_images_path');
    }

    /**
     * @return string|null
     */
    private function getDefaultStorage(): ?string
    {
        return self::STORAGE_MAP[(int)$this->parameterBag->get('default_storage')] ?? null;
    }

    private function getFilesystemOperator($storage): ?FilesystemOperator
    {
        if ($storage === self::STORAGE_FTP) {
            return $this->ftpStorage;
        } elseif ($storage === self::STORAGE_LOCAL) {
            return $this->localStorage;
        }

        return null;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }
}
