<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Schema\Resource;

use Devleand\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use Devleand\Yin\JsonApi\Request\JsonApiRequestInterface;
use Devleand\Yin\JsonApi\Schema\Link\ResourceLinks;

interface ResourceInterface
{
    /**
     * Provides information about the "type" member of the current resource.
     *
     * The method returns the type of the current resource.
     * @param mixed $object
     */
    public function getType($object): string;

    /**
     * Provides information about the "id" member of the current resource.
     *
     * The method returns the ID of the current resource which should be a UUID.
     * @param mixed $object
     */
    public function getId($object): string;

    /**
     * Provides information about the "meta" member of the current resource.
     *
     * The method returns an array of non-standard meta information about the resource. If
     * this array is empty, the member won't appear in the response.
     * @param mixed $object
     */
    public function getMeta($object): array;

    /**
     * Provides information about the "links" member of the current resource.
     *
     * The method returns a new ResourceLinks object if you want to provide linkage
     * data about the resource or null if it should be omitted from the response.
     * @param mixed $object
     */
    public function getLinks($object): ?ResourceLinks;

    /**
     * Provides information about the "attributes" member of the current resource.
     *
     * The method returns an array where the keys signify the attribute names,
     * while the values are callables receiving the domain object as an argument,
     * and they should return the value of the corresponding attribute.
     * @param mixed $object
     * @return callable[]
     */
    public function getAttributes($object): array;

    /**
     * Returns an array of relationship names which are included in the response by default.
     * @param mixed $object
     * @return string[]
     */
    public function getDefaultIncludedRelationships($object): array;

    /**
     * Provides information about the "relationships" member of the current resource.
     *
     * The method returns an array where the keys signify the relationship names,
     * while the values are callables receiving the domain object as an argument,
     * and they should return a new relationship instance (to-one or to-many).
     * @param mixed $object
     * @return callable[]
     */
    public function getRelationships($object): array;

    /**
     * @internal
     * @param mixed $object
     */
    public function initializeTransformation(JsonApiRequestInterface $request, $object, ExceptionFactoryInterface $exceptionFactory): void;

    /**
     * @internal
     */
    public function clearTransformation(): void;
}
