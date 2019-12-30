# Blue Canary PHP Client

[![Build Status](https://travis-ci.com/brightfish-be/blue-canary-php-client.svg?branch=master&label=Build&style=flat-square)](https://travis-ci.com/brightfish-be/blue-canary-php-client)
[![StyleCI](https://github.styleci.io/repos/230270770/shield?branch=master&style=flat-square)](https://github.styleci.io/repos/230270770)

**[WORK IN PROGRESS...]**
Guzzle-based PHP client for [Blue Canary](https://github.com/brightfish-be/blue-canary-dashboard), 
monitoring and metrics collection server.

## Logger usage examples
```
use Brightfish\BlueCanaryClient\Logger;

$client = new \GuzzleHttp\Client();

$logger = new Logger($client, [
    'base_uri' => 'http://canary.test',
    'api_version' => 'v1',
    'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
    'counter' => 'default.counter.1',
    'client_id' => 'some-id',
    'client_name' => 'some-name',
]);

// Send message and status code without metrics.
$logger->emergency('Whoops My App is out!');

// Send message and status code without metrics, overriding the global parameters.
$logger->emergency('Whoops My App is out again!', [
    'counter' => 'default.counter.2',
    'client_id' => 'some-id-2',
    'client_name' => 'some-name-2',
]);

// Send one metric with message and status code, using the global parameters.
$logger->metric('throughput', 30.3567, 'fps')->warning('Whoops, My App may have troubles!');

// Send two metrics with casting, without message and default OK status code
$logger->metric('duration', 3465.3567, 'sec', 'int')
    ->metric(new Metric('throughput', 30.3567, 'fps', 'int'))
    ->ok();

// Send OK async 
$promise = $logger->okAsync();
```

## Client usage examples
```
use Brightfish\BlueCanaryClient\Logger;

$guzzle = new \GuzzleHttp\Client();

$canary = new Client($guzzle, [
    'base_uri' => 'https://canary.app',
    'api_version' => 'v1',
    'counter' => 'default.counter.low',
    'uuid' => '11111111-b2ac-4ae4-9381-dcd4524dd7e7',
    'client_id' => 'some-id',
    'client_name' => 'some-name',
]);

// Collect multiple metrics without sending yet.
foreach ([] as $metric) {
    $canary->metric($metric['key'], $metric['value'], $metric['unit']);
}

// Send previously collected metrics to a another app/counter than defined globally.
$canary->post('11111111-b2ac-4ae4-9381-dcd4524dd7e7/new.counter');

// Send one metric to the globally defined app/counter pair,
// with default OK status code and no message.
$canary->get(new Metric('throughput', 30.3567, 'fps'));

// Send an async POST request setting the data manually, overriding the globally defined
// app/counter pair as well as the globally defined client id and name.
$canary->postAsync('11111111-b2ac-4ae4-9381-dcd4524dd7e7/new.counter', [
    'client_id' => 'machine-abc',
    'client_name' => 'Machine I',
    'status_code' => 0,
    'status_remark' => 'Info message',
    'generated_at' => 1576783076,
    'metrics' => [
        ['key' => '', 'value' => '', 'unit' => '', 'cast' => ''],
        ['key' => '', 'value' => '', 'unit' => ''],
    ]
]);

// Send an async GET request setting the data manually, overriding the globally defined 
// client id/name, to another Blue Canary instance, which uses another API version.
$canary->getAsync([
    'base_uri' => 'http://canary.org',
    'api_version' => 'v2',
    'counter' => 'counter.name',
    'uuid' => '4524dd7e-b2ac-4ae4-9381-dcd4524dd7e7',
    'client_id' => 'machine-123',
    'client_name' => 'Citizen Four',
    'status_code' => 5,
    'status_remark' => 'Whoopsie daisy',
    'generated_at' => 1576783076,
    'metrics' => [
        ['key' => '', 'value' => '', 'unit' => ''],
        ['key' => '', 'value' => '', 'unit' => '', 'cast' => ''],
    ]
]);
```

## License
GNU General Public License (GPL). Please see the license file for more information.
