<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Exception;

use Devleand\Yin\JsonApi\Schema\Error\Error;
use Devleand\Yin\JsonApi\Schema\Error\ErrorSource;

class ClientGeneratedIdRequired extends AbstractJsonApiException
{
    public function __construct()
    {
        parent::__construct("A client generated ID must be used!", 403);
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("403")
                ->setCode("CLIENT_GENERATED_ID_REQUIRED")
                ->setTitle("Required client generated ID")
                ->setDetail($this->getMessage())
                ->setSource(ErrorSource::fromPointer("/data/id")),
        ];
    }
}
