<?php

namespace Brightfish\BlueCanaryClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Blue Canary logger client.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Logger implements LoggerInterface
{
    const EMERGENCY = 7;
    const ALERT = 6;
    const CRITICAL = 5;
    const ERROR = 4;
    const WARNING = 3;
    const NOTICE = 2;
    const INFO = 1;
    const OK = 0;

    const DEBUG = 255;

    /**
     * System is unusable.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function emergency($message = '', array $parameters = []): ResponseInterface
    {
        return new Response();
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function alert($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement alert() method.
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function critical($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement critical() method.
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function error($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement error() method.
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function warning($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement warning() method.
    }

    /**
     * Normal but significant events.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function notice($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement notice() method.
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function info($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement info() method.
    }

    /**
     * Everything went fine, nothing to report.
     * Example: User logs in, SQL logs.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function ok($message = '', array $parameters = []): ResponseInterface
    {
        // TODO: Implement ok() method.
    }

    /**
     * Detailed debug information.
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     */
    public function debug($message, array $parameters = []): ResponseInterface
    {
        // TODO: Implement debug() method.
    }

    /**
     * Logs with an arbitrary level.
     * @param mixed $level
     * @param string $message
     * @param array $parameters
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $parameters = []): ResponseInterface
    {
        // TODO: Implement log() method.
    }
}
