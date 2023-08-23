<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Serializer;

use Psr\Http\Message\ServerRequestInterface;

use function json_decode;

class JsonDeserializer implements DeserializerInterface
{
    /**
     * @var int
     */
    private $options;

    /**
     * @var positive-int
     */
    private $depth;

    /**
     * @param positive-int $depth
     */
    public function __construct(int $options = 0, int $depth = 512)
    {
        $this->options = $options;
        $this->depth = $depth;
    }

    /**
     * @return array|mixed|null
     */
    public function deserialize(ServerRequestInterface $request)
    {
        return json_decode($request->getBody()->__toString(), true, $this->depth, $this->options);
    }
}
