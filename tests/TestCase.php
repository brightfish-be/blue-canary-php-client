<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /** @var Client */
    protected $guzzle;

    /** @inheritDoc */
    public function setUp(): void
    {
        parent::setUp();

        $response = new Response(200, [], '');
        $mock = new MockHandler([$response]);

        $stack = HandlerStack::create($mock);

        $this->guzzle = new Client([
            'handler' => $stack,
        ]);
    }
}
