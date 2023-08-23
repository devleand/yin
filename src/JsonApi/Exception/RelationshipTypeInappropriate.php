<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Exception;

use Devleand\Yin\JsonApi\Schema\Error\Error;
use Devleand\Yin\JsonApi\Schema\Error\ErrorSource;

class RelationshipTypeInappropriate extends AbstractJsonApiException
{
    /**
     * @var string
     */
    protected $relationshipName;

    /**
     * @var string
     */
    protected $currentRelationshipType;

    /**
     * @var string
     */
    protected $expectedRelationshipType;

    public function __construct(
        string $relationshipName,
        string $currentRelationshipType,
        string $expectedRelationshipType
    ) {
        parent::__construct(
            "The provided relationship '$relationshipName' is of type of $currentRelationshipType, but " .
            ($expectedRelationshipType !== "" ? "$expectedRelationshipType is" : "it is not the one which is") . " expected!",
            400
        );
        $this->relationshipName = $relationshipName;
        $this->currentRelationshipType = $currentRelationshipType;
        $this->expectedRelationshipType = $expectedRelationshipType;
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("400")
                ->setCode("RELATIONSHIP_TYPE_INAPPROPRIATE")
                ->setTitle("Relationship type is inappropriate")
                ->setDetail($this->getMessage())
                ->setSource(ErrorSource::fromPointer("/data/relationships/$this->relationshipName")),
        ];
    }

    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }

    public function getCurrentRelationshipType(): string
    {
        return $this->currentRelationshipType;
    }

    public function getExpectedRelationshipType(): string
    {
        return $this->expectedRelationshipType;
    }
}
