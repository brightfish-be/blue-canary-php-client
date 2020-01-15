<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature;

use Brightfish\BlueCanary\Exceptions\ClientException;
use Brightfish\BlueCanary\Client;
use Brightfish\BlueCanary\Metric;
use GuzzleHttp\Promise\Promise;
use Tests\TestCase;

class LoggerFeatureTest extends TestCase
{
    public function test_min_log_level()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new-counter',
        ]);

        $r = $logger->setLevel('alert')->info();

        $this->assertTrue($r === null);
    }

    public function test_metrics_and_post_data()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new-counter',
        ]);

        $logger->metric('duration', 3465.3567, 'sec', 'int')
            ->metric(new Metric('throughput', 30.3567, 'fps'))
            ->info('Information metrics');

        $req = $this->requestHistory[0]['request'];
        $body = json_decode($req->getBody(), true);

        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals(6, $body['status_code']);
        $this->assertEquals('Information metrics', $body['status_remark']);
        $this->assertEquals(3465, $body['metrics'][0]['value']);
        $this->assertEquals('throughput', $body['metrics'][1]['key']);
    }

    public function test_parameter_overruling()
    {
        $logger = new Client($this->guzzle, [
            'base_uri' => 'https://blue-1.net',
            'uuid' => '22222222-b2ac-4ae4-9381-dcd4524dd7e7',
            'api_version' => 'v1',
            'counter' => 'test.counter.1',
        ]);

        $logger->notice('Hep', [
            'base_uri' => 'https://blue-2.net',
            'uuid' => '33333333-b2ac-4ae4-9381-dcd4524dd7e7',
            'api_version' => 'v2',
            'counter' => 'test.counter.2',
        ]);

        $url = (string)$this->requestHistory[0]['request']->getUri();

        $this->assertStringContainsString('https://blue-2.net/api/v2/event/33333333-b2ac-4ae4-9381-dcd4524dd7e7/test.counter.2', $url);
    }

    public function test_get_params()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '22222222-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'test.counter',
            'client_id' => 'some-id',
            'client_name' => 'some-name',
        ]);

        $logger->error('Whoopsie daisy');

        $url = (string)$this->requestHistory[0]['request']->getUri();

        $this->assertStringContainsString('client_id=some-id&client_name=some-name&status_code=3&status_remark=Whoopsie+daisy', $url);
    }

    public function test_async_methods()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new-counter',
        ]);

        $this->assertTrue($logger->noticeAsync() instanceof Promise);
    }

    public function test_missing_app_uuid_throws_exception()
    {
        $logger = new Client($this->guzzle);

        $this->expectException(ClientException::class);

        $logger->emergency();
    }

    public function test_wrong_app_uuid_throws_exception()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-0ae4-9381-dcd4524dd7e7',
        ]);

        $this->expectException(ClientException::class);

        $logger->alert();
    }

    public function test_missing_counter_throws_exception()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
        ]);

        $this->expectException(ClientException::class);

        $logger->warning();
    }

    public function test_wrong_counter_throws_exception()
    {
        $logger = new Client($this->guzzle, [
            'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
            'counter' => 'new.counter."',
        ]);

        $this->expectException(ClientException::class);

        $logger->info();
    }
}
