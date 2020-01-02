<?php

namespace Brightfish\BlueCanary\Laravel;

use Brightfish\BlueCanary\Logger;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Laravel logger driver class.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class LogDriver
{
    /**
     * Create a Blue Canary logger instance.
     * @param array $config
     * @return LoggerInterface
     */
    public function __invoke(array $config)
    {
        $level = $config['level'] ?? 7;

        unset($config['level']);

        $parameters = [
            'base_uri' => $config['base_uri'] ?? 'http://canary.stage',
            'api_version' => $config['api_version'] ?? 'v1',
            'client_id' => $config['client_id'] ?? null,
            'client_name' => $config['client_name'] ?? null,
            'uuid' => $config['uuid'] ?? null,
            'counter' => $config['counter'] ?? null,
        ];

        return (new Logger(new Client(), $parameters))->setLevel($level);
    }
}
