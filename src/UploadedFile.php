<?php

namespace SimpleWeb\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use SimpleWeb\Http\Message\Factory\StreamFactory;

final class UploadedFile implements UploadedFileInterface
{
    /** @var StreamInterface|null */
    private $stream;

    /** @var int|null */
    private $size;

    /** @var int */
    private $error;

    /** @var string|null */
    private $clientFilename;

    /** @var string|null */
    private $clientMediaType;

    /** @var bool */
    private $moved = false;

    /**
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ) {
        $this->stream = $stream;
        $this->size = $size ?? $stream->getSize();
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @inheritDoc
     */
    public function moveTo($targetPath): void
    {
        if ($this->moved) {
            throw new \RuntimeException('The uploaded file has already been moved.');
        }

        if ($this->error !== \UPLOAD_ERR_OK) {
            throw new \RuntimeException('There was an error during upload and the file cannot be moved.');
        }

        if (!\is_string($targetPath) || '' === $targetPath) {
            throw new \InvalidArgumentException('The target path must be a non-empty string.');
        }

        $directory = \dirname($targetPath);

        if (!\is_writable($directory)) {
            throw new \RuntimeException('The target path directory is not writable.');
        }

        $this->writeStreamToFile($targetPath);

        $this->stream->close();
        $this->stream = null;

        $this->moved = true;
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        if (!$this->stream instanceof StreamInterface) {
            throw new \RuntimeException('The uploaded file stream is not available.');
        }

        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * @param string $path
     */
    private function writeStreamToFile(string $path): void
    {
        $source = $this->getStream();

        if ($source->isSeekable()) {
            $source->rewind();
        }

        $target = (new StreamFactory())->createStreamFromFile($path, 'wb');

        while (!$source->eof()) {
            $target->write($source->read(4096));
        }

        $target->close();
    }
}
