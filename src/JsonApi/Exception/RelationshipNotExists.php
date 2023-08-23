<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Exception;

use Devleand\Yin\JsonApi\Schema\Error\Error;

class RelationshipNotExists extends AbstractJsonApiException
{
    /**
     * @var string
     */
    protected $relationship;

    public function __construct(string $relationship)
    {
        parent::__construct("The requested relationship '$relationship' does not exist!", 404);
        $this->relationship = $relationship;
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("404")
                ->setCode("RELATIONSHIP_NOT_EXISTS")
                ->setTitle("The requested relationship does not exist!")
                ->setDetail($this->getMessage()),
        ];
    }

    public function getRelationship(): string
    {
        return $this->relationship;
    }
}
