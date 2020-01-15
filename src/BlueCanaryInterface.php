<?php

namespace Brightfish\BlueCanary;

use Brightfish\BlueCanary\Exceptions\MetricException;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

interface BlueCanaryInterface extends LoggerInterface
{
    /**
     * Merge parameters with default, set initial uri parts.
     * @param ClientInterface $guzzle
     * @param array $parameters
     */
    public function __construct(ClientInterface $guzzle, array $parameters = []);

    /**
     * Add a metric for the next request.
     * @param string|Metric $key
     * @param float $value
     * @param string|null $unit
     * @param string $cast
     * @return Client
     * @throws MetricException
     */
    public function metric($key, float $value = 0, ?string $unit = null, string $cast = 'float'): self;

    /**
     * Trim and cast parameters.
     * @param array $parameters
     * @return array
     */
    public function getParameters(array $parameters): array;

    /**
     * Return the API URL, while optionally validating its parts.
     * @return string
     */
    public function getUri(): string;

    /**
     * Regex-check if the given string is a valid UUID.
     * @param string $uuid
     * @return bool
     */
    public function isUuidValid(string $uuid): bool;

    /**
     * Regex-check if the given string is a valid counter name.
     * @param string $name
     * @return bool
     */
    public function isCounterNameValid(string $name): bool;

    /**
     * Return all currently stashed metrics.
     * @return Metric[]
     */
    public function getMetrics(): array;
}
