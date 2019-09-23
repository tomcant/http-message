<?php

namespace Tests\SimpleWeb\Http\Message;

use PHPUnit\Framework\TestCase;
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
        $this->assertInstanceOf(UploadedFile::class, $this->uploadedFile);
    }
}
