<?php

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

interface CommandInputBuilderInterface
{
    public function __construct(
        ParameterBagInterface $parameterBag,
        FilesystemOperator $ftpStorage,
        FilesystemOperator $localStorage
    );

    public function build(InputInterface $input, OutputInterface $output);

    public function setCommand(Command $command);
}
