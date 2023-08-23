<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Exception;

use PHPUnit\Framework\TestCase;
use Devleand\Yin\JsonApi\Exception\ResourceTypeUnacceptable;

class ResourceTypeUnacceptableTest extends TestCase
{
    /**
     * @test
     */
    public function getErrors(): void
    {
        $exception = $this->createException("", []);

        $errors = $exception->getErrorDocument()->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals("409", $errors[0]->getStatus());
    }

    /**
     * @test
     */
    public function getCurrentType(): void
    {
        $exception = $this->createException("book", []);

        $type = $exception->getCurrentType();

        $this->assertEquals("book", $type);
    }

    /**
     * @test
     */
    public function getAcceptedTypes(): void
    {
        $exception = $this->createException("", ["book"]);

        $types = $exception->getAcceptedTypes();

        $this->assertEquals(["book"], $types);
    }

    private function createException(string $type, array $acceptedTypes): ResourceTypeUnacceptable
    {
        return new ResourceTypeUnacceptable($type, $acceptedTypes);
    }
}
