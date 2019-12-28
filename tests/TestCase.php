<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /** @var Client */
    protected $guzzle;

    /** @var array */
    protected $requestHistory = [];

    /** @inheritDoc */
    public function setUp(): void
    {
        parent::setUp();

        $history = Middleware::history($this->requestHistory);
        $response = new Response(200, [], '');
        $mock = new MockHandler([$response]);

        $stack = HandlerStack::create($mock);

        $stack->push($history);

        $this->guzzle = new Client([
            'handler' => $stack,
        ]);
    }
}
