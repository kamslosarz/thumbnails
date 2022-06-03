<?php

namespace App\Tests\Command;

use App\Command\CreateThumbnails;
use App\Entity\Thumbnail;
use App\Entity\ThumbnailCommandInput;
use App\Service\CommandInputBuilderInterface;
use App\Service\RecursiveFileLoader;
use App\Service\ThumbnailFactory;
use ArrayIterator;
use Exception;
use League\Flysystem\FilesystemOperator;
use Mockery;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateThumbnailsTest extends KernelTestCase
{
    /**
     * @throws Exception
     */
    public function testShouldExecuteCommandSuccess()
    {
        $input = Mockery::mock(InputInterface::class)
            ->shouldReceive('bind')
            ->getMock()
            ->shouldReceive('isInteractive')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('hasArgument')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('validate')
            ->getMock();

        $output = Mockery::mock(OutputInterface::class)
            ->shouldReceive('writeln')
            ->with('<fg=green>Input data is valid, starting...</>')
            ->getMock()
            ->shouldReceive('writeln')
            ->with('<fg=green>Success</>')
            ->getMock()
            ->shouldReceive('write')
            ->with('<fg=gray>Creating thumbnail from <fg=white>path</> ... </>')
            ->getMock()
            ->shouldReceive('write')
            ->with('<fg=gray>Saving thumbnail as <fg=white>filename.png</> ... </>')
            ->getMock();

        $filesystemOperator = Mockery::mock(FilesystemOperator::class)
            ->shouldReceive('write')
            ->getMock();

        $commandInput = Mockery::mock(ThumbnailCommandInput::class)
            ->shouldReceive('isValid')
            ->andReturnTrue()
            ->getMock()
            ->shouldReceive('getFilesystemOperator')
            ->andReturn($filesystemOperator)
            ->getMock()
            ->shouldReceive('getPath')
            ->once()
            ->andReturn('path')
            ->getMock();

        $commandInputBuilder = Mockery::mock(CommandInputBuilderInterface::class)
            ->shouldReceive('setCommand')
            ->getMock()
            ->shouldReceive('build')
            ->andReturn($commandInput)
            ->getMock();

        $recursiveFileLoader = Mockery::mock(RecursiveFileLoader::class)
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(new ArrayIterator([
                'path',
            ]))
            ->getMock();

        $thumbnailFactory = Mockery::mock(ThumbnailFactory::class)
            ->shouldReceive('getInstance')
            ->andReturn(
                Mockery::mock(Thumbnail::class)
                    ->shouldReceive('create')
                    ->getMock()
                    ->shouldReceive('getFilename')
                    ->andReturn('filename.png')
                    ->getMock()
                    ->shouldReceive('getContents')
                    ->andReturn('test')
                    ->getMock()
            )
            ->getMock();

        /** @var ContainerInterface $container */
        /** @var CommandInputBuilderInterface $commandInputBuilder */
        /** @var RecursiveFileLoader $recursiveFileLoader */
        /** @var ThumbnailFactory $thumbnailFactory */
        $createThumbnails = new CreateThumbnails($commandInputBuilder, $recursiveFileLoader, $thumbnailFactory);

        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        $exitCode = $createThumbnails->run($input, $output);

        $commandInputBuilder->shouldHaveReceived('setCommand')->once();
        $input->shouldHaveReceived('bind')->once();
        $input->shouldHaveReceived('isInteractive')->once();
        $input->shouldHaveReceived('hasArgument')->once();
        $input->shouldHaveReceived('validate')->once();
        $output->shouldHaveReceived('write')->times(2);
        $output->shouldHaveReceived('writeln')->times(3);
        $commandInput->shouldHaveReceived('isValid')->once();
        $commandInput->shouldHaveReceived('getFilesystemOperator')->once();
        $commandInput->shouldHaveReceived('getPath')->once();
        $filesystemOperator->shouldReceive('write')->once();

        $this->assertEquals(0, $exitCode);
    }

    public function testShouldExecuteCommandAndFailedOnInputValidation()
    {
        $input = Mockery::mock(InputInterface::class)
            ->shouldReceive('bind')
            ->getMock()
            ->shouldReceive('isInteractive')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('hasArgument')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('validate')
            ->getMock();

        $output = Mockery::mock(OutputInterface::class);
        $commandInput = Mockery::mock(ThumbnailCommandInput::class)
            ->shouldReceive('isValid')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('getLastErrorMessage')
            ->andReturn('error')
            ->getMock();

        $commandInputBuilder = Mockery::mock(CommandInputBuilderInterface::class)
            ->shouldReceive('setCommand')
            ->getMock()
            ->shouldReceive('build')
            ->andReturn($commandInput)
            ->getMock();

        $recursiveFileLoader = Mockery::mock(RecursiveFileLoader::class);
        $thumbnailFactory = Mockery::mock(ThumbnailFactory::class);

        /** @var ContainerInterface $container */
        /** @var CommandInputBuilderInterface $commandInputBuilder */
        /** @var RecursiveFileLoader $recursiveFileLoader */
        /** @var ThumbnailFactory $thumbnailFactory */
        $createThumbnails = new CreateThumbnails($commandInputBuilder, $recursiveFileLoader, $thumbnailFactory);

        /** @var InputInterface $input */
        /** @var OutputInterface $output */

        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('error');
        $createThumbnails->run($input, $output);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldExecuteCommandAndFailedOnThumbnailCreate()
    {
        $input = Mockery::mock(InputInterface::class)
            ->shouldReceive('bind')
            ->getMock()
            ->shouldReceive('isInteractive')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('hasArgument')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('validate')
            ->getMock();

        $output = Mockery::mock(OutputInterface::class)
            ->shouldReceive('writeln')
            ->with('<fg=green>Input data is valid, starting...</>')
            ->once()
            ->getMock()
            ->shouldReceive('write')
            ->with('<fg=gray>Creating thumbnail from <fg=white>path</> ... </>')
            ->once()
            ->getMock()
            ->shouldReceive('writeln')
            ->once()
            ->with('<fg=red>Failed. error creating thumbnails</>')
            ->getMock();

        $filesystemOperator = Mockery::mock(FilesystemOperator::class)
            ->shouldReceive('write')
            ->getMock();

        $commandInput = Mockery::mock(ThumbnailCommandInput::class)
            ->shouldReceive('isValid')
            ->andReturnTrue()
            ->getMock()
            ->shouldReceive('getFilesystemOperator')
            ->andReturn($filesystemOperator)
            ->getMock()
            ->shouldReceive('getPath')
            ->once()
            ->andReturn('path')
            ->getMock();

        $recursiveFileLoader = Mockery::mock(RecursiveFileLoader::class)
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(new ArrayIterator([
                'path',
            ]))
            ->getMock();

        $commandInputBuilder = Mockery::mock(CommandInputBuilderInterface::class)
            ->shouldReceive('setCommand')
            ->getMock()
            ->shouldReceive('build')
            ->andReturn($commandInput)
            ->getMock();

        $thumbnailFactory = Mockery::mock(ThumbnailFactory::class)
            ->shouldReceive('getInstance')
            ->andReturn(
                Mockery::mock(Thumbnail::class)
                    ->shouldReceive('create')
                    ->andThrow(new Exception('error creating thumbnails'))
                    ->getMock()
            )
            ->getMock();

        /** @var ContainerInterface $container */
        /** @var CommandInputBuilderInterface $commandInputBuilder */
        /** @var RecursiveFileLoader $recursiveFileLoader */
        /** @var ThumbnailFactory $thumbnailFactory */
        $createThumbnails = new CreateThumbnails($commandInputBuilder, $recursiveFileLoader, $thumbnailFactory);

        /** @var InputInterface $input */
        /** @var OutputInterface $output */

        $exitCode = $createThumbnails->run($input, $output);

        $output->shouldHaveReceived('writeln')->times(2);
        $this->assertEquals(0, $exitCode);
    }

    public function testShouldExecuteCommandAndFailedOnFilesystemWrite()
    {
        $input = Mockery::mock(InputInterface::class)
            ->shouldReceive('bind')
            ->getMock()
            ->shouldReceive('isInteractive')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('hasArgument')
            ->andReturnFalse()
            ->getMock()
            ->shouldReceive('validate')
            ->getMock();

        $output = Mockery::mock(OutputInterface::class)
            ->shouldReceive('writeln')
            ->with('<fg=green>Input data is valid, starting...</>')
            ->getMock()
            ->shouldReceive('writeln')
            ->with('<fg=green>Success</>')
            ->getMock()
            ->shouldReceive('writeln')
            ->with('<fg=red>Failed. </>')
            ->getMock()
            ->shouldReceive('write')
            ->with('<fg=gray>Creating thumbnail from <fg=white>path</> ... </>')
            ->getMock()
            ->shouldReceive('write')
            ->with('<fg=gray>Saving thumbnail as <fg=white>filename.png</> ... </>')
            ->getMock();

        $filesystemOperator = Mockery::mock(FilesystemOperator::class)
            ->shouldReceive('write')
            ->andThrows(Exception::class)
            ->getMock();

        $commandInput = Mockery::mock(ThumbnailCommandInput::class)
            ->shouldReceive('isValid')
            ->andReturnTrue()
            ->getMock()
            ->shouldReceive('getFilesystemOperator')
            ->andReturn($filesystemOperator)
            ->getMock()
            ->shouldReceive('getPath')
            ->once()
            ->andReturn('path')
            ->getMock();

        $commandInputBuilder = Mockery::mock(CommandInputBuilderInterface::class)
            ->shouldReceive('setCommand')
            ->getMock()
            ->shouldReceive('build')
            ->andReturn($commandInput)
            ->getMock();

        $recursiveFileLoader = Mockery::mock(RecursiveFileLoader::class)
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(new ArrayIterator([
                'path',
            ]))
            ->getMock();

        $thumbnailFactory = Mockery::mock(ThumbnailFactory::class)
            ->shouldReceive('getInstance')
            ->andReturn(
                Mockery::mock(Thumbnail::class)
                    ->shouldReceive('create')
                    ->getMock()
                    ->shouldReceive('getFilename')
                    ->andReturn('filename.png')
                    ->getMock()
                    ->shouldReceive('getContents')
                    ->andReturn('test')
                    ->getMock()
            )
            ->getMock();

        /** @var ContainerInterface $container */
        /** @var CommandInputBuilderInterface $commandInputBuilder */
        /** @var RecursiveFileLoader $recursiveFileLoader */
        /** @var ThumbnailFactory $thumbnailFactory */
        $createThumbnails = new CreateThumbnails($commandInputBuilder, $recursiveFileLoader, $thumbnailFactory);

        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        $exitCode = $createThumbnails->run($input, $output);

        $commandInputBuilder->shouldHaveReceived('setCommand')->once();
        $input->shouldHaveReceived('bind')->once();
        $input->shouldHaveReceived('isInteractive')->once();
        $input->shouldHaveReceived('hasArgument')->once();
        $input->shouldHaveReceived('validate')->once();
        $output->shouldHaveReceived('write')->times(2);
        $output->shouldHaveReceived('writeln')->times(3);
        $commandInput->shouldHaveReceived('isValid')->once();
        $commandInput->shouldHaveReceived('getFilesystemOperator')->once();
        $commandInput->shouldHaveReceived('getPath')->once();
        $filesystemOperator->shouldReceive('write')->once();
        $filesystemOperator->shouldReceive('writeln')->times(3);

        $this->assertEquals(0, $exitCode);
    }
}

