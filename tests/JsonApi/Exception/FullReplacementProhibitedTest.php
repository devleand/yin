<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Exception;

use PHPUnit\Framework\TestCase;
use Devleand\Yin\JsonApi\Exception\FullReplacementProhibited;

class FullReplacementProhibitedTest extends TestCase
{
    /**
     * @test
     */
    public function getErrors(): void
    {
        $exception = $this->createException("authors");

        $errors = $exception->getErrorDocument()->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals("403", $errors[0]->getStatus());
    }

    /**
     * @test
     */
    public function getRelationshipName(): void
    {
        $exception = $this->createException("authors");

        $relationshipName = $exception->getRelationshipName();

        $this->assertEquals("authors", $relationshipName);
    }

    private function createException(string $relationshipName): FullReplacementProhibited
    {
        return new FullReplacementProhibited($relationshipName);
    }
}
