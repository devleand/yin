<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Exception;

use Psr\Http\Message\ResponseInterface;
use Devleand\Yin\JsonApi\Schema\Document\ErrorDocument;
use Devleand\Yin\JsonApi\Schema\Document\ErrorDocumentInterface;
use Devleand\Yin\JsonApi\Schema\Error\Error;

class ResponseBodyInvalidJson extends AbstractJsonApiException
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var string
     */
    protected $lintMessage;

    /**
     * @var bool
     */
    protected $includeOriginalBody;

    public function __construct(ResponseInterface $response, string $lintMessage, bool $includeOriginalBody)
    {
        parent::__construct("Response body is an invalid JSON document: '$lintMessage'!", 500);
        $this->response = $response;
        $this->lintMessage = $lintMessage;
        $this->includeOriginalBody = $includeOriginalBody;
    }

    public function getErrorDocument(): ErrorDocumentInterface
    {
        $errorDocument = new ErrorDocument($this->getErrors());

        if ($this->includeOriginalBody) {
            $errorDocument->setMeta(["original" => $this->response->getBody()->__toString()]);
        }

        return $errorDocument;
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("500")
                ->setCode("RESPONSE_BODY_INVALID_JSON")
                ->setTitle("Response body is an invalid JSON document")
                ->setDetail($this->getMessage()),
        ];
    }

    public function getLintMessage(): string
    {
        return $this->lintMessage;
    }
}
