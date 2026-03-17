<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\Bootstrap;

class ApplicationIntegrityTest extends TestCase
{
    public function test_bootstrap_builds_application(): void
    {
        $bootstrap = new Bootstrap('test');

        $this->assertNotNull($bootstrap->createOrderUseCase());
        $this->assertNotNull($bootstrap->processOrderUseCase());
    }
}
