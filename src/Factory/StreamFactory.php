<?php

namespace SimpleWeb\Http\Message\Factory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use SimpleWeb\Http\Message\Stream;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = \fopen('php://temp', 'rw+');
        \fwrite($resource, $content);

        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @\fopen($filename, $mode);

        if (false === $resource) {
            throw new \RuntimeException(sprintf('Could not open file %s with mode %s', $filename, $mode));
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
