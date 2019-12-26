<?php

namespace Tests\Feature;

use Brightfish\BlueCanaryClient\Logger;
use Tests\TestCase;

class LoggerFeatureTest extends TestCase
{
    public function test_emergency()
    {
        $logger = new Logger();

        $logger->emergency();

        $this->assertTrue(true);
    }
}
