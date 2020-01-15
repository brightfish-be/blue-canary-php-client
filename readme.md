# Blue Canary PHP Client

[![Build Status](https://travis-ci.com/brightfish-be/blue-canary-php-client.svg?branch=master&label=Build&style=flat-square)](https://travis-ci.com/brightfish-be/blue-canary-php-client)
[![StyleCI](https://github.styleci.io/repos/230270770/shield?branch=master&style=flat-square)](https://github.styleci.io/repos/230270770)

**[WORK IN PROGRESS...]**
Guzzle-based PHP client for [Blue Canary](https://github.com/brightfish-be/blue-canary-dashboard), 
monitoring and metrics collection server.

## Usage examples
```
use Brightfish\BlueCanary\Client;

$client = new \GuzzleHttp\Client();

$logger = new Client($client, [
    'base_uri' => 'https://canary.stage',
    'api_version' => 'v1',
    'uuid' => '5b8c58e9-b2ac-4ae4-9381-dcd4524dd7e7',
    'counter' => 'default.counter.1',
    'client_id' => 'some-id',
    'client_name' => 'Some name',
]);

// Send message and status code without metrics.
$logger->emergency('Whoops, my app is out!');

// Send message and status code without metrics, overriding the global parameters.
$logger->emergency('Whoops, my app is out again!', [
    'counter' => 'default.counter.2',
    'client_id' => 'some-id-2',
    'client_name' => 'Some Name 2',
]);

// Send one metric with message and status code, using the global parameters.
$logger->metric('throughput', 30.3567, 'fps')->warning('Whoops, my app may have troubles!');

// Send two metrics with casting, without message and default OK status code
$logger->metric('duration', 3465.3567, 'sec', 'int')
    ->metric(new Metric('throughput', 30.3567, 'fps', 'int'))
    ->info();

// Send INFO async 
$promise = $logger->infoAsync();
```

## Laravel usage
1. Edit your `.env` file:
```
BLUE_CANARY_CLIENT_ID=my-client-machine-id
BLUE_CANARY_CLIENT_NAME="My client machine"
```
2. Reference a custom log driver in `config/logging.php` under `channels`:
```
'canary' => [
    'driver' => 'custom',
    'via' => Brightfish\BlueCanary\Laravel\LogDriver::class,
    'client_id' => env('BLUE_CANARY_CLIENT_ID'),
    'client_name' => env('BLUE_CANARY_CLIENT_NAME'),
],
```
3. Usage:
```
// With facade
Log::emergency('Hello world!', $parameters);
// From container
app('log')->alert('Hello world!', $parameters);
// With instance
/** @var Brightfish\BlueCanary\Client $client */
$client = app('log')->driver('canary')->getLogger();
$logger->metric(...)->metric(...)->warning();
```

## License
GNU General Public License (GPL). Please see the license file for more information.
