<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi;

use Psr\Http\Message\ResponseInterface;
use Devleand\Yin\JsonApi\Exception\DefaultExceptionFactory;
use Devleand\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use Devleand\Yin\JsonApi\Exception\InclusionUnsupported;
use Devleand\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use Devleand\Yin\JsonApi\Exception\SortingUnsupported;
use Devleand\Yin\JsonApi\Hydrator\HydratorInterface;
use Devleand\Yin\JsonApi\Hydrator\UpdateRelationshipHydratorInterface;
use Devleand\Yin\JsonApi\Request\JsonApiRequestInterface;
use Devleand\Yin\JsonApi\Request\Pagination\PaginationFactory;
use Devleand\Yin\JsonApi\Response\Responder;
use Devleand\Yin\JsonApi\Serializer\JsonSerializer;
use Devleand\Yin\JsonApi\Serializer\SerializerInterface;

class JsonApi
{
    /**
     * @var JsonApiRequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        JsonApiRequestInterface $request,
        ResponseInterface $response,
        ?ExceptionFactoryInterface $exceptionFactory = null,
        ?SerializerInterface $serializer = null
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->exceptionFactory = $exceptionFactory ?? new DefaultExceptionFactory();
        $this->serializer = $serializer ?? new JsonSerializer();
    }

    public function getRequest(): JsonApiRequestInterface
    {
        return $this->request;
    }

    public function setRequest(JsonApiRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getExceptionFactory(): ExceptionFactoryInterface
    {
        return $this->exceptionFactory;
    }

    public function setExceptionFactory(ExceptionFactoryInterface $exceptionFactory): void
    {
        $this->exceptionFactory = $exceptionFactory;
    }

    public function getPaginationFactory(): PaginationFactory
    {
        return new PaginationFactory($this->request);
    }

    public function respond(): Responder
    {
        return new Responder($this->request, $this->response, $this->exceptionFactory, $this->serializer);
    }

    /**
     * @param mixed $object
     * @return mixed
     */
    public function hydrate(HydratorInterface $hydrator, $object)
    {
        return $hydrator->hydrate($this->request, $this->exceptionFactory, $object);
    }

    /**
     * @param mixed $object
     * @return mixed
     */
    public function hydrateRelationship(
        string $relationship,
        UpdateRelationshipHydratorInterface $hydrator,
        $object
    ) {
        return $hydrator->hydrateRelationship($relationship, $this->request, $this->exceptionFactory, $object);
    }

    /**
     * Disables inclusion of related resources.
     *
     * If the current request asks for inclusion of related resources, it throws an InclusionNotSupported exception.
     *
     * @throws InclusionUnsupported|JsonApiExceptionInterface
     */
    public function disableIncludes(): void
    {
        if ($this->request->getQueryParam("include") !== null) {
            throw $this->exceptionFactory->createInclusionUnsupportedException($this->request);
        }
    }

    /**
     * Disables sorting.
     *
     * If the current request contains sorting criteria, it throws a SortingNotSupported exception.
     *
     * @throws SortingUnsupported|JsonApiExceptionInterface
     */
    public function disableSorting(): void
    {
        if ($this->request->getQueryParam("sort") !== null) {
            throw $this->exceptionFactory->createSortingUnsupportedException($this->request);
        }
    }
}
