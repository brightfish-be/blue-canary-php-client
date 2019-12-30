# Blue Canary PHP Client

[![Build Status](https://travis-ci.com/brightfish-be/blue-canary-php-client.svg?branch=master&label=Build&style=flat-square)](https://travis-ci.com/brightfish-be/blue-canary-php-client)
[![StyleCI](https://github.styleci.io/repos/230270770/shield?branch=master&style=flat-square)](https://github.styleci.io/repos/230270770)

**[WORK IN PROGRESS...]**
Guzzle-based PHP client for [Blue Canary](https://github.com/brightfish-be/blue-canary-dashboard), 
monitoring and metrics collection server.

## Usage examples
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

## License
GNU General Public License (GPL). Please see the license file for more information.
