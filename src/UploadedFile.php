<?php

namespace SimpleWeb\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

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

    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
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
     * @return void
     */
    private function throwIfStreamIsNotAvailable(): void
    {
        if (!$this->stream instanceof StreamInterface) {
            throw new \RuntimeException('');
        }
    }
}
