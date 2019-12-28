<?php

namespace Brightfish\BlueCanaryClient;

use Brightfish\BlueCanaryClient\Exceptions\ClientException;
use Brightfish\BlueCanaryClient\Exceptions\LoggerException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Blue Canary logger client.
 * @method PromiseInterface emergencyAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface alertAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface criticalAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface warningAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface errorAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface noticeAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface infoAsync(string $message = '', array $parameters = [])
 * @method PromiseInterface okAsync(string $message = '', array $parameters = [])
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Logger extends Client implements LoggerInterface
{
    /** @var array */
    const LEVELS = [
        'emergency' => 7,
        'alert' => 6,
        'critical' => 5,
        'error' => 4,
        'warning' => 3,
        'notice' => 2,
        'info' => 1,
        'ok' => 0,
        'debug' => 255,
    ];

    /**
     * System is unusable.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function emergency($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function alert($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function critical($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function error($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function warning($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Normal but significant events.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function notice($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function info($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Everything went fine, nothing to report.
     * Example: User logs in, SQL logs.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    public function ok($message = '', array $parameters = [])
    {
        return $this->handleRequest(__FUNCTION__, $message, $parameters);
    }

    /**
     * Detailed debug information.
     * @param string $message
     * @param array $parameters
     * @return void
     * @throws LoggerException
     */
    public function debug($message = '', array $parameters = [])
    {
        throw new LoggerException('This method is currently not supported.');
    }

    /**
     * Logs with an arbitrary level.
     * @param mixed $level
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws InvalidArgumentException
     * @throws ClientException
     */
    public function log($level, $message = '', array $parameters = [])
    {
        if (is_numeric($level)) {
            $level = array_flip(self::LEVELS)[$level];
        }

        return $this->handleRequest($level, $message, $parameters);
    }

    /**
     * Handle all interface-level methods uniformly and perform the request.
     * @param string $name
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface|PromiseInterface
     * @throws ClientException
     */
    protected function handleRequest(string $name, string $message, array $parameters)
    {
        $parameters = array_merge($parameters, [
            'status_code' => self::LEVELS[$name],
            'status_remark' => $message,
        ]);

        return $this->request($parameters);
    }

    /**
     * Allow all interface-level methods can be called as async, eg. `alertAsync`, 'infoAsync'.
     * @param string $name
     * @param array $arguments
     * @return PromiseInterface
     * @throws LoggerException
     */
    public function __call($name, $arguments): PromiseInterface
    {
        $method = str_replace('Async', '', $name);

        if (!method_exists($this, $method)) {
            throw new LoggerException('This method does not exist.');
        }

        $msg = (string)array_shift($arguments);
        $parameters = (array)array_shift($arguments);

        $parameters['async'] = true;

        return $this->$method($msg, $parameters);
    }
}
