<?php

namespace Tests\SimpleWeb\Http\Message;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use SimpleWeb\Http\Message\Stream;

/**
 * @covers \SimpleWeb\Http\Message\Stream
 */
class StreamTest extends TestCase
{
    /** @var resource */
    private $resource;

    /** @var Stream */
    private $stream;

    protected function setUp(): void
    {
        $this->resource = \fopen('php://memory', 'w');
        $this->stream = new Stream($this->resource);
    }

    protected function tearDown(): void
    {
        if (\is_resource($this->resource)) {
            \fclose($this->resource);
        }
    }

    public function test_it_can_be_instantiated_with_a_valid_resource()
    {
        $this->assertInstanceOf(StreamInterface::class, $this->stream);
    }

    public function test_it_cannot_be_instantiated_with_an_invalid_resource()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not available.');

        new Stream('Invalid stream');
    }

    public function test_it_can_be_cast_to_a_string()
    {
        \fwrite($this->resource, 'A string');

        $this->assertEquals('A string', (string) $this->stream);
    }

    public function test_it_rewinds_the_stream_when_casting_to_a_string()
    {
        \fwrite($this->resource, 'A string');
        \fseek($this->resource, 1);

        $this->assertEquals('A string', (string) $this->stream);
    }

    public function test_it_returns_the_empty_string_when_casting_an_unreadable_stream()
    {
        $stream = new Stream(\fopen('php://stdout', 'w'));

        $this->assertEquals('', (string) $stream);
    }

    public function test_it_clears_the_resource_when_closing()
    {
        $this->stream->close();

        $this->assertFalse(\is_resource($this->resource));
    }

    public function test_it_returns_the_resource_when_detaching()
    {
        $this->assertSame($this->resource, $this->stream->detach());
    }

    public function test_it_returns_null_when_detaching_a_closed_resource()
    {
        \fclose($this->resource);

        $this->assertNull($this->stream->detach());
    }

    public function test_it_can_get_the_size_of_the_stream()
    {
        \fwrite($this->resource, 'A string');

        $this->assertEquals(\strlen('A string'), $this->stream->getSize());
    }

    public function test_it_returns_null_when_getting_the_size_of_a_closed_stream()
    {
        \fclose($this->resource);

        $this->assertNull($this->stream->getSize());
    }

    public function test_it_can_tell_the_current_position_of_the_stream()
    {
        \fwrite($this->resource, 'A string');

        $this->assertEquals(\strlen('A string'), $this->stream->tell());
    }

    public function test_it_throws_a_RuntimeException_when_telling_a_closed_stream()
    {
        \fclose($this->resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not available.');

        $this->stream->tell();
    }

    public function test_it_can_test_for_the_end_of_an_open_stream()
    {
        $this->assertFalse($this->stream->eof());

        \fread($this->resource, 1);

        $this->assertTrue($this->stream->eof());
    }

    public function test_it_returns_true_when_testing_for_the_end_of_a_closed_stream()
    {
        $this->assertFalse($this->stream->eof());

        \fclose($this->resource);

        $this->assertTrue($this->stream->eof());
    }

    public function test_it_can_check_a_stream_is_seekable()
    {
        $this->assertTrue($this->stream->isSeekable());
    }

    public function test_it_can_check_a_stream_is_not_seekable()
    {
        $stream = new Stream(\fopen('php://stdout', 'w'));

        $this->assertFalse($stream->isSeekable());
    }

    public function test_it_can_check_a_closed_stream_is_not_seekable()
    {
        \fclose($this->resource);

        $this->assertFalse($this->stream->isSeekable());
    }

    public function test_it_can_seek_on_a_seekable_stream()
    {
        \fwrite($this->resource, 'A string');

        $this->stream->seek(1);

        $this->assertEquals(1, \ftell($this->resource));
    }

    public function test_it_throws_a_RuntimeException_when_seeking_to_an_unseekable_position()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to seek to the specified position.');

        $this->stream->seek(-1);
    }

    public function test_it_throws_a_RuntimeException_when_seeking_on_a_closed_stream()
    {
        \fclose($this->resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not available.');

        $this->stream->seek(0);
    }

    public function test_it_can_rewind_to_the_beginning_of_the_stream()
    {
        \fwrite($this->resource, 'A string');

        $this->stream->rewind();

        $this->assertEquals(0, \ftell($this->resource));
    }

    public function test_it_throws_a_RuntimeException_when_rewinding_on_a_closed_stream()
    {
        \fclose($this->resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not available.');

        $this->stream->rewind();
    }

    public function test_it_can_check_permissions_on_a_write_only_stream()
    {
        $stream = new Stream(\fopen('php://stdout', 'w'));

        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->isWritable());
    }

    public function test_it_can_check_permissions_on_a_closed_stream()
    {
        \fclose($this->resource);

        $this->assertFalse($this->stream->isReadable());
        $this->assertFalse($this->stream->isWritable());
    }

    public function test_it_can_write_to_a_stream()
    {
        $this->stream->write('A string');

        $this->assertEquals('A string', \stream_get_contents($this->resource, \strlen('A string'), 0));
    }

    public function test_it_throws_a_RuntimeException_when_writing_to_an_unwritable_stream()
    {
        $stream = new Stream(\fopen('php://stdin', 'r'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not writable.');

        $stream->write('A string');
    }

    public function test_it_can_check_permissions_on_a_read_only_stream()
    {
        $stream = new Stream(\fopen('php://stdin', 'r'));

        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
    }

    public function test_it_can_read_from_a_stream()
    {
        \fwrite($this->resource, 'A string');
        \fseek($this->resource, 0);

        $this->assertEquals('A string', $this->stream->read(\strlen('A string')));
    }

    public function test_it_throws_a_RuntimeException_when_failing_to_read_from_a_stream()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to read from the stream resource.');

        $this->stream->read(-1);
    }

    public function test_it_throws_a_RuntimeException_when_reading_from_an_unreadable_stream()
    {
        $stream = new Stream(\fopen('php://stdout', 'w'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The stream resource is not readable.');

        $stream->read(1);
    }

    public function test_it_can_get_an_associative_array_of_metadata()
    {
        $metadata = $this->stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('seekable', $metadata);
    }

    public function test_it_can_get_a_specific_metadata_key()
    {
        $this->assertIsBool($this->stream->getMetadata('seekable'));
    }

    public function test_it_returns_null_when_getting_metadata_for_an_invalid_key()
    {
        $this->assertNull($this->stream->getMetadata('unknown'));
    }

    public function test_it_returns_null_when_getting_metadata_for_a_closed_stream()
    {
        \fclose($this->resource);

        $this->assertNull($this->stream->getMetadata());
    }
}
