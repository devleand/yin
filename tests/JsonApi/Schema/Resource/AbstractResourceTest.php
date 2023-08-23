<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Schema\Resource;

use PHPUnit\Framework\TestCase;
use Devleand\Yin\JsonApi\Exception\DefaultExceptionFactory;
use Devleand\Yin\JsonApi\Schema\Resource\ResourceInterface;
use Devleand\Yin\JsonApi\Transformer\ResourceTransformation;
use Devleand\Yin\Tests\JsonApi\Double\StubJsonApiRequest;
use Devleand\Yin\Tests\JsonApi\Double\StubResource;

class AbstractResourceTest extends TestCase
{
    /**
     * @test
     */
    public function initializeTransformation(): void
    {
        $resource = $this->createResource();
        $transformation = $this->createTransformation($resource);

        $resource->initializeTransformation(
            $transformation->request,
            $transformation->object,
            $transformation->exceptionFactory
        );

        $this->assertEquals($transformation->request, $resource->getRequest());
        $this->assertEquals($transformation->object, $resource->getObject());
        $this->assertEquals($transformation->exceptionFactory, $resource->getExceptionFactory());
    }

    /**
     * @test
     */
    public function clearTransformation(): void
    {
        $resource = $this->createResource();
        $transformation = $this->createTransformation($resource);

        $resource->initializeTransformation(
            $transformation->request,
            $transformation->object,
            $transformation->exceptionFactory
        );
        $resource->clearTransformation();

        $this->assertNull($resource->getRequest());
        $this->assertNull($resource->getObject());
        $this->assertNull($resource->getExceptionFactory());
    }

    protected function createResource(): StubResource
    {
        return new StubResource();
    }

    private function createTransformation(ResourceInterface $resource): ResourceTransformation
    {
        return new ResourceTransformation(
            $resource,
            [],
            "",
            new StubJsonApiRequest(),
            "",
            "",
            "",
            new DefaultExceptionFactory()
        );
    }
}
