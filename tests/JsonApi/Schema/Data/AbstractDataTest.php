<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Schema\Data;

use PHPUnit\Framework\TestCase;
use Devleand\Yin\Tests\JsonApi\Double\DummyData;

class AbstractDataTest extends TestCase
{
    /**
     * @test
     */
    public function setPrimaryResources(): void
    {
        $dummyData = new DummyData();
        $dummyData->setPrimaryResources(
            [
                ["type" => "user", "id" => "1"],
                ["type" => "user", "id" => "2"],
            ]
        );

        $this->assertTrue($dummyData->hasPrimaryResource("user", "1"));
        $this->assertTrue($dummyData->hasPrimaryResource("user", "2"));
    }

    /**
     * @test
     */
    public function addNotYetIncludedPrimaryResource(): void
    {
        $dummyData = new DummyData();
        $dummyData->addPrimaryResource(["type" => "user", "id" => "1"]);

        $this->assertTrue($dummyData->hasPrimaryResource("user", "1"));
    }

    /**
     * @test
     */
    public function addAlreadyIncludedPrimaryResource(): void
    {
        $dummyData = new DummyData();
        $dummyData->addIncludedResource(["type" => "user", "id" => "1"]);
        $dummyData->addPrimaryResource(["type" => "user", "id" => "1"]);

        $this->assertFalse($dummyData->hasIncludedResource("user", "1"));
        $this->assertTrue($dummyData->hasPrimaryResource("user", "1"));
    }
}
