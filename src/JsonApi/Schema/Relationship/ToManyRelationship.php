<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Schema\Relationship;

use Devleand\Yin\JsonApi\Schema\Data\DataInterface;
use Devleand\Yin\JsonApi\Transformer\ResourceTransformation;
use Devleand\Yin\JsonApi\Transformer\ResourceTransformer;

class ToManyRelationship extends AbstractRelationship
{
    /**
     * @internal
     * @return array|false|null
     */
    protected function transformData(
        ResourceTransformation $transformation,
        ResourceTransformer $resourceTransformer,
        DataInterface $data,
        array $defaultRelationships
    ) {
        if ($this->resource === null) {
            return false;
        }

        /** @var iterable $object */
        $object = $this->getData();
        if (empty($object)) {
            return [];
        }

        $result = [];
        foreach ($object as $item) {
            $resourceIdentifier = $this->transformResourceIdentifier($transformation, $resourceTransformer, $data, $item, $defaultRelationships);

            if ($resourceIdentifier !== null) {
                $result[] = $resourceIdentifier;
            }
        }

        return $result;
    }
}
