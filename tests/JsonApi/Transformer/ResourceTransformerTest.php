<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Transformer;

use Laminas\Diactoros\ServerRequest as DiactorosServerRequest;
use PHPUnit\Framework\TestCase;
use Devleand\Yin\JsonApi\Exception\DefaultExceptionFactory;
use Devleand\Yin\JsonApi\Exception\InclusionUnrecognized;
use Devleand\Yin\JsonApi\Exception\RelationshipNotExists;
use Devleand\Yin\JsonApi\Request\JsonApiRequest;
use Devleand\Yin\JsonApi\Request\JsonApiRequestInterface;
use Devleand\Yin\JsonApi\Schema\Link\ResourceLinks;
use Devleand\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use Devleand\Yin\JsonApi\Schema\Resource\ResourceInterface;
use Devleand\Yin\JsonApi\Serializer\JsonDeserializer;
use Devleand\Yin\JsonApi\Transformer\ResourceTransformation;
use Devleand\Yin\JsonApi\Transformer\ResourceTransformer;
use Devleand\Yin\Tests\JsonApi\Double\DummyData;
use Devleand\Yin\Tests\JsonApi\Double\StubJsonApiRequest;
use Devleand\Yin\Tests\JsonApi\Double\StubResource;

class ResourceTransformerTest extends TestCase
{
    /**
     * @test
     */
    public function transformToResourceIdentifierWhenObjectIsNull(): void
    {
        $resource = $this->createResource();

        $resourceIdentifier = $this->toResourceIdentifier($resource, null);

        $this->assertNull($resourceIdentifier);
    }

    /**
     * @test
     */
    public function transformToResourceIdentifierWhenObjectIsNotNull(): void
    {
        $resource = $this->createResource("user", "1");

        $resourceIdentifier = $this->toResourceIdentifier($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
            ],
            $resourceIdentifier
        );
    }

    /**
     * @test
     */
    public function transformToResourceIdentifierWithMeta(): void
    {
        $resource = $this->createResource("user", "1", ["abc" => "def"]);

        $resourceIdentifier = $this->toResourceIdentifier($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
                "meta" => ["abc" => "def"],
            ],
            $resourceIdentifier
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWhenNull(): void
    {
        $resource = $this->createResource("user", "1");

        $resourceObject = $this->toResourceObject($resource, null);

        $this->assertNull($resourceObject);
    }

    /**
     * @test
     */
    public function transformToResourceObjectWhenAlmostEmpty(): void
    {
        $resource = $this->createResource("user", "1");

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithMeta(): void
    {
        $resource = $this->createResource("", "", ["abc" => "def"]);

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "",
                "id" => "",
                "meta" => ["abc" => "def"],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithLinks(): void
    {
        $resource = $this->createResource("", "", [], new ResourceLinks());

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "",
                "id" => "",
                "links" => [],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithMetaAndLinks(): void
    {
        $resource = $this->createResource("user", "1", ["abc" => "def"], new ResourceLinks());

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
                "meta" => ["abc" => "def"],
                "links" => [],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithAttributes(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            ["abc" => "def"],
            new ResourceLinks(),
            [
                "full_name" => function (array $object, JsonApiRequestInterface $request) {
                    return $object["name"];
                },
                "birth" => function (array $object) {
                    return 2015 - $object["age"];
                },
            ]
        );

        $resourceObject = $this->toResourceObject(
            $resource,
            [
                "name" => "John Doe",
                "age" => "30",
            ]
        );

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
                "meta" => ["abc" => "def"],
                "links" => [],
                "attributes" => [
                    "full_name" => "John Doe",
                    "birth" => 1985,
                ],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithDefaultRelationship(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            ["father"],
            [
                "father" => function (array $object, JsonApiRequestInterface $request): ToOneRelationship {
                    return ToOneRelationship::create()
                        ->setData([""], new StubResource("user", "2"));
                },
            ]
        );

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
                "relationships" => [
                    "father" => [
                        "data" => [
                            "type" => "user",
                            "id" => "2",
                        ],
                    ],
                ],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithoutRelationships(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            [],
            [
                "father" => function (): ToOneRelationship {
                    return ToOneRelationship::create();
                },
            ]
        );

        $resourceObject = $this->toResourceObject($resource, [], StubJsonApiRequest::create(["fields" => ["user" => ""]]));

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithInvalidRelationship(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            ["father"],
            [
                "father" => function (): ToOneRelationship {
                    return ToOneRelationship::create();
                },
            ]
        );

        $this->expectException(InclusionUnrecognized::class);

        $this->toResourceObject($resource, [], StubJsonApiRequest::create(["include" => "mother"]));
    }

    /**
     * @test
     */
    public function transformToResourceObjectWithRelationships(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            [],
            [
                "father" => function (): ToOneRelationship {
                    return ToOneRelationship::create()
                        ->setData(null, new StubResource());
                },
            ]
        );

        $resourceObject = $this->toResourceObject($resource, []);

        $this->assertEquals(
            [
                "type" => "user",
                "id" => "1",
                "relationships" => [
                    "father" => [
                        "data" => null,
                    ],
                ],
            ],
            $resourceObject
        );
    }

    /**
     * @test
     */
    public function transformToRelationshipObjectWhenEmpty(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            [],
            []
        );

        $this->expectException(RelationshipNotExists::class);

        $this->toRelationshipObject($resource, [], null, "father");
    }

    /**
     * @test
     */
    public function transformToRelationshipObjectWhenNotFound(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            [],
            [
                "father" => function (): ToOneRelationship {
                    return ToOneRelationship::create()
                        ->setData(["Father Vader"], new StubResource("user", "2"));
                },
            ]
        );

        $this->expectException(RelationshipNotExists::class);

        $this->toRelationshipObject($resource, [], null, "mother");
    }

    /**
     * @test
     */
    public function transformToRelationshipObject(): void
    {
        $resource = $this->createResource(
            "user",
            "1",
            [],
            null,
            [],
            [],
            [
                "father" => function (): ToOneRelationship {
                    return ToOneRelationship::create()
                        ->setData(["Father Vader"], new StubResource("user", "2"));
                },
            ]
        );

        $resourceObject = $this->toRelationshipObject($resource, [], null, "father");

        $this->assertEquals(
            [
                "data" => [
                    "type" => "user",
                    "id" => "2",
                ],
            ],
            $resourceObject
        );
    }

    /**
     * @param mixed $object
     */
    private function toResourceIdentifier(
        ResourceInterface $resource,
        $object,
        ?JsonApiRequestInterface $request = null
    ): ?array {
        $transformation = new ResourceTransformation(
            $resource,
            $object,
            "",
            $request ?? new JsonApiRequest(
                new DiactorosServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            "",
            "",
            new DefaultExceptionFactory()
        );

        $transformer = new ResourceTransformer();

        return $transformer->transformToResourceIdentifier($transformation);
    }

    /**
     * @param mixed $object
     */
    private function toResourceObject(
        ResourceInterface $resource,
        $object,
        ?JsonApiRequestInterface $request = null
    ): ?array {
        $transformation = new ResourceTransformation(
            $resource,
            $object,
            "",
            $request ?? new JsonApiRequest(
                new DiactorosServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            "",
            "",
            new DefaultExceptionFactory()
        );

        $transformer = new ResourceTransformer();

        return $transformer->transformToResourceObject($transformation, new DummyData());
    }

    /**
     * @param mixed $object
     */
    private function toRelationshipObject(
        ResourceInterface $resource,
        $object,
        ?JsonApiRequestInterface $request = null,
        string $requestedRelationshipName = ""
    ): ?array {
        $transformation = new ResourceTransformation(
            $resource,
            $object,
            "",
            $request ?? new JsonApiRequest(
                new DiactorosServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            $requestedRelationshipName,
            $requestedRelationshipName,
            new DefaultExceptionFactory()
        );

        $transformer = new ResourceTransformer();

        return $transformer->transformToRelationshipObject($transformation, new DummyData());
    }

    private function createResource(
        string $type = "",
        string $id = "",
        array $meta = [],
        ?ResourceLinks $links = null,
        array $attributes = [],
        array $defaultRelationships = [],
        array $relationships = []
    ): StubResource {
        return new StubResource($type, $id, $meta, $links, $attributes, $defaultRelationships, $relationships);
    }

    protected function createResourceTransformer(): ResourceTransformer
    {
        return new ResourceTransformer();
    }
}
