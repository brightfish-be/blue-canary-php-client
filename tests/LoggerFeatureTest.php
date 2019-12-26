<?php

namespace Tests\Feature;

use Brightfish\BlueCanaryClient\Exceptions\BlueCanaryException;
use Brightfish\BlueCanaryClient\Logger;
use GuzzleHttp\Promise\Promise;
use Tests\TestCase;

class LoggerFeatureTest extends TestCase
{
    /*
    public function test_parameter_overruling()
    {
        $logger = new Logger($this->guzzle, [
            'uuid' => '22222222-b2ac-4ae4-9381-dcd4524dd7e7'
        ]);

        $logger->notice('Hep', [
            'uuid' => '33333333-b2ac-4ae4-9381-dcd4524dd7e7'
        ]);
    } */

    public function test_async_methods()
    {
        $logger = new Logger($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new-counter',
        ]);

        $r = $logger->noticeAsync();

        $this->assertTrue($r instanceof Promise);
    }

    public function test_missing_app_uuid_throws_exception()
    {
        $logger = new Logger($this->guzzle);

        $this->expectException(BlueCanaryException::class);

        $logger->emergency();
    }

    public function test_wrong_app_uuid_throws_exception()
    {
        $logger = new Logger($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-0ae4-9381-dcd4524dd7e7',
        ]);

        $this->expectException(BlueCanaryException::class);

        $logger->alert();
    }

    public function test_missing_counter_throws_exception()
    {
        $logger = new Logger($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
        ]);

        $this->expectException(BlueCanaryException::class);

        $logger->warning();
    }

    public function test_wrong_counter_throws_exception()
    {
        $logger = new Logger($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new.counter."',
        ]);

        $this->expectException(BlueCanaryException::class);

        $logger->warning();
    }
}
