<?php

namespace Brightfish\BlueCanaryClient;

use Brightfish\BlueCanaryClient\Exceptions\BlueCanaryException;
use GuzzleHttp\Client as BaseClient;
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
    /** @var BaseClient */
    protected $guzzle;

    /** @var array */
    protected $defaults = [
        'base_uri' => 'https://canary.test',
        'api_version' => 'v1',
        'client_id' => null,
        'client_name' => null,
        'counter' => null,
        'uuid' => null,
    ];

    /** @var array */
    protected $allowedParameters = [
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
     * Metrics to be send upon request. This is emptied after each request.
     * @var array
     */
    protected $metrics = [];

    /**
     * Merge parameters with default, set initial uri parts.
     * @param BaseClient $guzzle
     * @param array $parameters
     */
    public function __construct(BaseClient $guzzle, array $parameters = [])
    {
        $this->guzzle = $guzzle;

        $this->parameters = array_merge($this->defaults, $parameters);

        $this->setUri($this->parameters);
    }

    /**
     * Create a request instance and let Guzzle perform it.
     * @param array $parameters
     * @return \GuzzleHttp\PromiseInterface|ResponseInterface
     * @throws BlueCanaryException
     */
    protected function request(array $parameters)
    {
        $request = $this->createRequest($parameters);

        $guzzleOptions = $this->extractGuzzleOptions($parameters);

        if (empty($parameters['async'])) {
            unset($parameters['async']);
            return $this->guzzle->send($request, $guzzleOptions);
        }

        return $this->guzzle->sendAsync($request, $guzzleOptions);
    }

    /**
     * @param array $parameters
     * @return RequestInterface
     * @throws BlueCanaryException
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
        return \GuzzleHttp\json_encode([
            'form_params' => $this->getParameters($parameters),
        ]);
    }

    /**
     * Trim and cast parameters.
     * @param array $parameters
     * @return array
     */
    public function getParameters(array $parameters): array
    {
        $parameters = array_intersect_key($parameters, array_flip($this->allowedParameters));

        array_walk($parameters, function (&$value, $key) {
            switch ($key) {
                case 'status_code':
                    $value = (int)$value ?? 0;
                    break;
                default:
                    $value = $value ?? null;
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

        return str_replace($this->uri['api_version'], $this->uri['api_version'] . '/events', $uri);
    }

    /**
     * Check if the given url has the required parts present and well formatted.
     * @return Client
     * @throws BlueCanaryException
     */
    protected function validateUri(): self
    {
        foreach ($this->uri as $key => $value) {
            if (!$value) {
                throw new BlueCanaryException("A $key is missing.");
            }
        }

        if (!$this->isUuidValid($this->uri['uuid'])) {
            throw new BlueCanaryException('The app uuid is invalid.');
        }

        if (!$this->isCounterNameValid($this->uri['counter'])) {
            throw new BlueCanaryException('The counter name is invalid.');
        }

        if (strpos($this->uri['base_uri'], 'http') !== 0) {
            throw new BlueCanaryException('This protocol is not supported.');
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
        return array_diff_key($parameters, $this->defaults);
    }
}
