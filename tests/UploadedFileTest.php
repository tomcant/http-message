<?php

namespace Tests\SimpleWeb\Http\Message;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use SimpleWeb\Http\Message\Stream;
use SimpleWeb\Http\Message\UploadedFile;

class UploadedFileTest extends TestCase
{
    /** @var Stream */
    private $stream;

    /** @var UploadedFile */
    private $uploadedFile;

    /** @var string */
    private $targetPath;

    protected function setUp(): void
    {
        $this->stream = new Stream(\fopen('php://memory', 'w'));
        $this->uploadedFile = new UploadedFile($this->stream);
        $this->targetPath = \tempnam(\sys_get_temp_dir(), 'simple-web');
    }

    protected function tearDown(): void
    {
        if ($this->stream instanceof Stream) {
            $this->stream->close();
        }

        if (\file_exists($this->targetPath)) {
            \unlink($this->targetPath);
        }
    }

    public function test_it_can_be_instantiated()
    {
        $this->assertInstanceOf(UploadedFileInterface::class, $this->uploadedFile);
    }

    public function test_it_can_get_the_stream()
    {
        $this->assertInstanceOf(StreamInterface::class, $this->uploadedFile->getStream());
    }

    public function test_it_can_get_the_size()
    {
        $size = \rand(0, \PHP_INT_MAX);
        $uploadedFile = new UploadedFile($this->stream, $size);

        $this->assertEquals($size, $uploadedFile->getSize());
    }

    public function test_it_can_get_the_error()
    {
        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_NO_FILE);

        $this->assertEquals(\UPLOAD_ERR_NO_FILE, $uploadedFile->getError());
    }

    public function test_it_can_get_the_client_file_name()
    {
        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, 'filename.ext');

        $this->assertEquals('filename.ext', $uploadedFile->getClientFilename());
    }

    public function test_it_can_get_the_client_media_type()
    {
        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, null, 'media/type');

        $this->assertEquals('media/type', $uploadedFile->getClientMediaType());
    }

    public function test_it_moves_the_file_to_the_given_target_path()
    {
        $this->stream->write('A string');
        $this->uploadedFile->moveTo($this->targetPath);

        $this->assertFileExists($this->targetPath);
        $this->assertEquals('A string', \file_get_contents($this->targetPath));
    }

    public function test_it_throws_a_RuntimeException_when_attempting_to_move_the_file_multiple_times()
    {
        $this->uploadedFile->moveTo($this->targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file has already been moved.');

        $this->uploadedFile->moveTo($this->targetPath);
    }

    public function test_it_throws_a_RuntimeException_when_attempting_to_move_a_file_with_an_upload_error()
    {
        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_INI_SIZE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There was an error during upload and the file cannot be moved.');

        $uploadedFile->moveTo($this->targetPath);
    }

    public function test_it_throws_an_InvalidArgumentException_when_moving_the_file_with_a_null_target_path()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The target path must be a non-empty string.');

        $this->uploadedFile->moveTo(null);
    }

    public function test_it_throws_an_InvalidArgumentException_when_moving_the_file_with_an_empty_target_path()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The target path must be a non-empty string.');

        $this->uploadedFile->moveTo('');
    }

    public function test_it_throws_a_RuntimeException_when_moving_the_file_to_an_unwritable_target_path()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The target path directory is not writable.');

        $this->uploadedFile->moveTo(\uniqid() . \DIRECTORY_SEPARATOR . \uniqid());
    }

    public function test_it_throws_a_RuntimeException_when_getting_the_stream_after_moving_the_file()
    {
        $this->uploadedFile->moveTo($this->targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file stream is not available.');

        $this->uploadedFile->getStream();
    }
}
