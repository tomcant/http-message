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

    public function setUp(): void
    {
        $this->stream = new Stream(\fopen('php://memory', 'w'));
        $this->uploadedFile = new UploadedFile($this->stream);
    }

    protected function tearDown(): void
    {
        if ($this->stream instanceof Stream) {
            $this->stream->close();
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
}
