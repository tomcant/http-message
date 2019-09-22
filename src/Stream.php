<?php

namespace SimpleWeb\Http\Message;

use Psr\Http\Message\StreamInterface;

final class Stream implements StreamInterface
{
    /** @var resource */
    private $resource;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->throwIfResourceIsNotAvailable();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->isResourceAvailable()) {
            \fclose($this->resource);
            $this->detach();
        }
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        if (!$this->isResourceAvailable()) {
            return null;
        }

        $resource = $this->resource;
        unset($this->resource);

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        if (!$this->isResourceAvailable()) {
            return null;
        }

        $stats = \fstat($this->resource);

        if (false === $stats) {
            return null;
        }

        return $stats['size'];
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        $this->throwIfResourceIsNotAvailable();

        $position = \ftell($this->resource);

        if (false === $position) {
            throw new \RuntimeException('Failed to get the current position of the stream resource pointer.');
        }

        return $position;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return !$this->isResourceAvailable() || \feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->isResourceAvailable() && $this->getMetadata('seekable') ?? false;
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        $this->throwIfResourceIsNotAvailable();

        $result = \fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw new \RuntimeException('Failed to seek to the specified position.');
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        if (!$this->isResourceAvailable()) {
            return false;
        }

        $metadata = $this->getMetadata();

        return \strpbrk($metadata['mode'], 'waxc+') !== false;
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        $this->throwIfResourceIsNotAvailable();

        if (!$this->isWritable()) {
            throw new \RuntimeException('The stream resource is not writable.');
        }

        $bytesWritten = \fwrite($this->resource, $string);

        if (false === $bytesWritten) {
            throw new \RuntimeException('Failed to write to the stream resource.');
        }

        return $bytesWritten;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        if (!$this->isResourceAvailable()) {
            return false;
        }

        $metadata = $this->getMetadata();

        return \strpbrk($metadata['mode'], 'r+') !== false;
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        $this->throwIfResourceIsNotAvailable();

        if (!$this->isReadable()) {
            throw new \RuntimeException('The stream resource is not readable.');
        }

        $data = \fread($this->resource, $length);

        if (false === $data) {
            throw new \RuntimeException('Failed to read from the stream resource.');
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        $this->throwIfResourceIsNotAvailable();

        if (!$this->isReadable()) {
            throw new \RuntimeException('The stream resource is not readable.');
        }

        $contents = \stream_get_contents($this->resource);

        if (false === $contents) {
            throw new \RuntimeException('Failed to read from the stream resource.');
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        if (!$this->isResourceAvailable()) {
            return null;
        }

        $metadata = \stream_get_meta_data($this->resource);

        if ($key !== null) {
            return $metadata[$key] ?? null;
        }

        return $metadata;
    }

    /**
     * @return void
     */
    private function throwIfResourceIsNotAvailable(): void
    {
        if (!$this->isResourceAvailable()) {
            throw new \RuntimeException('The stream resource is not available.');
        }
    }

    /**
     * @return bool
     */
    private function isResourceAvailable(): bool
    {
        return \is_resource($this->resource) && \get_resource_type($this->resource) === 'stream';
    }
}
