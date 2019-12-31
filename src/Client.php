<?php

namespace Brightfish\BlueCanary;

use Brightfish\BlueCanary\Exceptions\ClientException;
use Brightfish\BlueCanary\Exceptions\MetricException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Blue Canary HTTP client.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Client
{
    /** @var ClientInterface */
    protected $guzzle;

    /** @var array */
    protected $defaults = [
        'base_uri' => 'https://canary.stage',
        'api_version' => 'v1',
        'client_id' => null,
        'client_name' => null,
        'counter' => null,
        'uuid' => null,
    ];

    /** @var array */
    protected $allowedDataKeys = [
        'client_id', 'client_name', 'status_code', 'status_remark', 'generated_at', 'metrics',
    ];

    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $uri = [
        'base_uri' => '',
        'api_version' => '',
        'uuid' => '',
        'counter' => '',
    ];

    /**
     * Metrics to be send upon request - this is emptied after each request.
     * @var Metric[]
     */
    protected $metrics = [];

    /**
     * Merge parameters with default, set initial uri parts.
     * @param ClientInterface $guzzle
     * @param array $parameters
     */
    public function __construct(ClientInterface $guzzle, array $parameters = [])
    {
        $this->guzzle = $guzzle;

        $this->parameters = array_merge($this->defaults, $parameters);

        $this->setUri($this->parameters);
    }

    /**
     * Add a metric for the next request.
     * @param string|Metric $key
     * @param float $value
     * @param string $unit
     * @param string $cast
     * @return Client
     * @throws MetricException
     */
    public function metric($key, float $value = 0, ?string $unit = null, string $cast = 'float'): self
    {
        $this->metrics[] = $key instanceof Metric ? $key : new Metric($key, $value, $unit, $cast);

        return $this;
    }

    /**
     * Create a request instance and let Guzzle perform it.
     * @param array $parameters
     * @return \GuzzleHttp\PromiseInterface|ResponseInterface
     * @throws ClientException
     */
    protected function request(array $parameters)
    {
        $request = $this->createRequest($parameters);

        $guzzleOptions = $this->extractGuzzleOptions($parameters);

        $this->clearMetrics();

        if (empty($parameters['async'])) {
            return $this->guzzle->send($request, $guzzleOptions);
        }

        unset($parameters['async']);

        return $this->guzzle->sendAsync($request, $guzzleOptions);
    }

    /**
     * @param array $parameters
     * @return RequestInterface
     * @throws ClientException
     */
    private function createRequest(array $parameters): RequestInterface
    {
        # Overrule parameters defined at instantiation.
        $parameters = array_merge($this->parameters, $parameters);

        $method = $this->getMethod();

        $uri = $this->setUri($parameters)->validateUri()->getUri();

        if ($method === 'GET') {
            $uri .= $this->buildGetParameters($parameters);
        } else {
            $body = $this->buildPostBody($parameters);
        }

        return new Request($method, $uri, [], $body ?? '');
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function buildGetParameters(array $parameters): string
    {
        return '?' . http_build_query($this->getParameters($parameters));
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function buildPostBody(array $parameters): string
    {
        $parameters['metrics'] = array_map(function (Metric $metric) {
            return $metric->toArray();
        }, $this->metrics);

        return \GuzzleHttp\json_encode($this->getParameters($parameters));
    }

    /**
     * Trim and cast parameters.
     * @param array $parameters
     * @return array
     */
    public function getParameters(array $parameters): array
    {
        $parameters = array_intersect_key($parameters, array_flip($this->allowedDataKeys));

        array_walk($parameters, function (&$value, $key) {
            switch ($key) {
                case 'status_code':
                    $value = (int)($value ?? 0);
                    break;
                default:
                    $value = $value ?: null;
                    break;
            }
        });

        return $parameters;
    }

    /**
     * Set the parts of the URL as array values - falls back to the previous one,
     * if the presently given is null.
     * @param array $parts
     * @return Client
     */
    protected function setUri(array $parts): self
    {
        foreach ($this->uri as $key => $value) {
            $this->uri[$key] = trim($parts[$key] ?? $value ?? $this->defaults[$key], '/');
        }

        return $this;
    }

    /**
     * Return the API URL, while optionally validating its parts.
     * @return string
     */
    public function getUri(): string
    {
        $uri = implode('/', $this->uri);

        return str_replace($this->uri['api_version'], 'api/' . $this->uri['api_version'] . '/event', $uri);
    }

    /**
     * Check if the given url has the required parts present and well formatted.
     * @return Client
     * @throws ClientException
     */
    protected function validateUri(): self
    {
        foreach ($this->uri as $key => $value) {
            if (!$value) {
                throw new ClientException("A $key is missing.");
            }
        }

        if (!$this->isUuidValid($this->uri['uuid'])) {
            throw new ClientException('The app uuid is invalid.');
        }

        if (!$this->isCounterNameValid($this->uri['counter'])) {
            throw new ClientException('The counter name is invalid.');
        }

        if (strpos($this->uri['base_uri'], 'http') !== 0) {
            throw new ClientException('This protocol is not supported.');
        }

        return $this;
    }

    /**
     * Regex-check if the given string is a valid UUID.
     * @param string $uuid
     * @return bool
     */
    public function isUuidValid(string $uuid): bool
    {
        return preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $uuid);
    }

    /**
     * Regex-check if the given string is a valid counter name.
     * @param string $name
     * @return bool
     */
    public function isCounterNameValid(string $name): bool
    {
        return preg_match('/^[a-z0-9\-_.]{6,255}$/i', $name);
    }

    /**
     * Return the most apt HTTP method for current request.
     * @return string
     */
    public function getMethod(): string
    {
        return $this->metrics ? 'POST' : 'GET';
    }

    /**
     * Distinguish and return the Guzzle options from the given parameter array.
     * @param array $parameters
     * @return array
     */
    protected function extractGuzzleOptions(array $parameters)
    {
        return array_filter($parameters, function ($key) {
            return !(in_array($key, $this->allowedDataKeys) || isset($this->defaults[$key]));
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Return all currently stashed metrics.
     * @return Metric[]
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Empty out the metrics array.
     * @return void
     */
    protected function clearMetrics(): void
    {
        $this->metrics = [];
    }
}
