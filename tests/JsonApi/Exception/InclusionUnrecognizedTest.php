<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Exception;

use PHPUnit\Framework\TestCase;
use Devleand\Yin\JsonApi\Exception\InclusionUnrecognized;

class InclusionUnrecognizedTest extends TestCase
{
    /**
     * @test
     */
    public function getErrors(): void
    {
        $exception = $this->createException([]);

        $errors = $exception->getErrorDocument()->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals("400", $errors[0]->getStatus());
    }

    /**
     * @test
     */
    public function getIncludes(): void
    {
        $exception = $this->createException(["a", "b", "c"]);

        $includes = $exception->getUnrecognizedIncludes();

        $this->assertEquals(["a", "b", "c"], $includes);
    }

    private function createException(array $includes): InclusionUnrecognized
    {
        return new InclusionUnrecognized($includes);
    }
}
