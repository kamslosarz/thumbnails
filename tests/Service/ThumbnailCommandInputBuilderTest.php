<?php

namespace App\Tests\Service;

use App\Service\ThumbnailCommandInputBuilder;
use League\Flysystem\FilesystemOperator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ThumbnailCommandInputBuilderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider commandInputSuccessDataProvider
     */
    public function testShouldBuildThumbnailCommandInputSuccess(string $path, string $defaultStorage)
    {
        $pathQuestionMock = Mockery::mock(Question::class)
            ->shouldReceive('ask')
            ->andReturnUsing(function (InputInterface $input, OutputInterface $output, Question $question) {
                return $question->getDefault();
            })
            ->getMock();

        $storageQuestionMock = Mockery::mock(Question::class)
            ->shouldReceive('ask')
            ->andReturnUsing(function (InputInterface $input, OutputInterface $output, Question $question) {
                return $question->getDefault();
            })
            ->getMock();

        $command = Mockery::mock(Command::class)
            ->shouldReceive('getHelper')
            ->andReturnValues([
                'path' => $pathQuestionMock,
                'storage' => $storageQuestionMock,
            ])->getMock()
            ->shouldReceive('setHelperSet')
            ->getMock();

        $parameterBag = Mockery::mock(ParameterBagInterface::class)
            ->shouldReceive('get')
            ->with('default_original_images_path')
            ->andReturn($path)
            ->getMock()
            ->shouldReceive('get')
            ->with('default_storage')
            ->andReturn($defaultStorage)
            ->getMock();

        $ftpStorage = Mockery::mock(FilesystemOperator::class);
        $localStorage = Mockery::mock(FilesystemOperator::class);

        /** @var Command $command */
        /** @var ParameterBagInterface $parameterBag */
        $builder = new ThumbnailCommandInputBuilder($parameterBag, $ftpStorage, $localStorage);
        $builder->setCommand($command);

        $input = Mockery::mock(InputInterface::class);
        $output = Mockery::mock(OutputInterface::class);

        $thumbnailCommandInput = $builder->build($input, $output);

        $this->assertEquals($path, $thumbnailCommandInput->getPath());
        if ($defaultStorage === ThumbnailCommandInputBuilder::STORAGE_LOCAL) {
            $this->assertEquals($localStorage, $thumbnailCommandInput->getFilesystemOperator());
        } else {
            $this->assertEquals($ftpStorage, $thumbnailCommandInput->getFilesystemOperator());
        }
        $this->assertEquals(null, $thumbnailCommandInput->getLastErrorMessage());

        $pathQuestionMock->shouldHaveReceived('ask');
        $storageQuestionMock->shouldHaveReceived('ask');
        $command->shouldHaveReceived('setHelperSet');
    }

    public function testShouldBuildThumbnailCommandInputAndFailOnInvalidStorage()
    {

        $pathQuestionMock = Mockery::mock(Question::class)
            ->shouldReceive('ask')
            ->andReturnUsing(function (InputInterface $input, OutputInterface $output, Question $question) {
                return $question->getDefault();
            })
            ->getMock();

        $storageQuestionMock = Mockery::mock(Question::class)
            ->shouldReceive('ask')
            ->andReturn('invalid storage')
            ->getMock();

        $command = Mockery::mock(Command::class)
            ->shouldReceive('getHelper')
            ->andReturnValues([
                'path' => $pathQuestionMock,
                'storage' => $storageQuestionMock,
            ])->getMock()
            ->shouldReceive('setHelperSet')
            ->getMock();

        $parameterBag = Mockery::mock(ParameterBagInterface::class)
            ->shouldReceive('get')
            ->with('default_original_images_path')
            ->andReturn('default_original_images_path')
            ->getMock()
            ->shouldReceive('get')
            ->with('default_storage')
            ->andReturn('default_storage')
            ->getMock();

        $ftpStorage = Mockery::mock(FilesystemOperator::class);
        $localStorage = Mockery::mock(FilesystemOperator::class);

        /** @var Command $command */
        /** @var ParameterBagInterface $parameterBag */
        $builder = new ThumbnailCommandInputBuilder($parameterBag, $ftpStorage, $localStorage);
        $builder->setCommand($command);

        $input = Mockery::mock(InputInterface::class);
        $output = Mockery::mock(OutputInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('Invalid storage "invalid storage"');

        $builder->build($input, $output);
    }

    public function commandInputSuccessDataProvider(): array
    {
        return [
            'case local directory' => [
                '/local/path',
                ThumbnailCommandInputBuilder::STORAGE_LOCAL,
            ],
            'case ftp' => [
                '/local/path',
                ThumbnailCommandInputBuilder::STORAGE_FTP,
            ],
        ];
    }
}
