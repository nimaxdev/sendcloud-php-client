<?php

namespace Picqer\Carriers\SendCloud;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Iterator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class Connection
{
    protected $headers = [];

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Connection
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        $this->client = null;
        return $this;
    }

    /**
     * @return Connection
     */
    public function setHeadersJson()
    {
        return $this->setHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * @return Connection
     */
    public function setHeadersPdf()
    {
        return $this->setHeaders([
            'Accept' => 'application/pdf',
        ]);
    }

    /**
     * @return Connection
     */
    public function setHeadersZpl()
    {
        return $this->setHeaders([
            'Accept' => 'application/zpl',
        ]);
    }

    private $apiUrl = 'https://panel.sendcloud.sc/api/v2/';
    private $apiKey;
    private $apiSecret;
    private $partnerId;

    /**
     * Contains the HTTP client (Guzzle)
     * @var Client
     */
    private $client;

    /**
     * Array of inserted middleWares
     * @var array
     */
    protected $middleWares = [];

    public function __construct(string $apiKey, string $apiSecret, ?string $partnerId = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->partnerId = $partnerId;
        $this->setHeadersJson();
    }

    public function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $handlerStack = HandlerStack::create();
        foreach ($this->middleWares as $middleWare) {
            $handlerStack->push($middleWare);
        }

        $clientConfig = [
            'base_uri' => $this->apiUrl(),
            'headers'  => $this->getHeaders(),
            'auth'     => [$this->apiKey, $this->apiSecret],
            'handler'  => $handlerStack,
        ];

        if (!is_null($this->partnerId)) {
            $clientConfig['headers']['Sendcloud-Partner-Id'] = $this->partnerId;
        }

        $this->client = new Client($clientConfig);

        return $this->client;
    }

    public function insertMiddleWare($middleWare): void
    {
        $this->middleWares[] = $middleWare;
    }

    public function apiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Return the api key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Perform a GET request
     * @param string|UriInterface $url
     * @param array               $params
     * @return array
     * @throws SendCloudApiException
     */
    public function get($url, $params = []): array
    {
        try {
            $result = $this->client()->get($url, ['query' => $params]);
            return $this->parseResponse($result);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->parseResponse($e->getResponse());
            }

            if ($statusCode = $e->getResponse()) {
                $statusCode = $statusCode->getStatusCode();
            } else {
                $statusCode = 0;
            }
            throw new SendCloudApiException(
                'SendCloud error: (no error message provided)' . $e->getResponse(), $statusCode
            );
        }
    }

    /**
     * Perform a POST request
     * @param string|UriInterface                                    $url
     * @param string|null|resource|StreamInterface|callable|Iterator $body
     * @return array
     * @throws SendCloudApiException
     */
    public function post($url, $body): array
    {
        try {
            $result = $this->client()->post($url, ['body' => $body]);
            return $this->parseResponse($result);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->parseResponse($e->getResponse());
            }

            throw new SendCloudApiException('SendCloud error: (no error message provided)' . $e->getResponse(), $e->getResponse()->getStatusCode());
        }
    }

    /**
     * Perform PUT request
     * @param string|UriInterface                                    $url
     * @param string|null|resource|StreamInterface|callable|Iterator $body
     * @return array
     * @throws SendCloudApiException
     */
    public function put($url, $body): array
    {
        try {
            $result = $this->client()->put($url, ['body' => $body]);
            return $this->parseResponse($result);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->parseResponse($e->getResponse());
            }

            throw new SendCloudApiException('SendCloud error: (no error message provided)' . $e->getResponse(), $e->getResponse()->getStatusCode());
        }
    }

    /**
     * Perform DELETE request
     * @param string|UriInterface $url
     * @return array
     * @throws SendCloudApiException
     */
    public function delete($url): array
    {
        try {
            $result = $this->client()->delete($url);
            return $this->parseResponse($result);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->parseResponse($e->getResponse());
            }

            throw new SendCloudApiException('SendCloud error: (no error message provided)' . $e->getResponse(), $e->getResponse()->getStatusCode());
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array Parsed JSON result
     * @throws SendCloudApiException
     */
    public function parseResponse(ResponseInterface $response): array
    {
        try {
            // Rewind the response (middlewares might have read it already)
            $response->getBody()->rewind();

            $responseBody = $response->getBody()->getContents();
            $resultArray = json_decode($responseBody, true);

            if (!is_array($resultArray)) {
                throw new SendCloudApiException(sprintf('SendCloud error %s: %s', $response->getStatusCode(), $responseBody), $response->getStatusCode());
            }

            if (array_key_exists('error', $resultArray)
                && is_array($resultArray['error'])
                && array_key_exists('message', $resultArray['error'])
            ) {
                throw new SendCloudApiException('SendCloud error: ' . $resultArray['error']['message'], $resultArray['error']['code']);
            }
            // handle cancel parcel return
            if (array_key_exists('message', $resultArray) && $response->getStatusCode() >= 400) {
                throw new SendCloudApiException('SendCloud error: ' . $resultArray['message'], $response->getStatusCode());
            }

            return $resultArray;
        } catch (RuntimeException $e) {
            throw new SendCloudApiException('SendCloud error: ' . $e->getMessage());
        }
    }

    /**
     * Returns the selected environment
     *
     * @return string
     * @deprecated
     */
    public function getEnvironment(): string
    {
        return 'live';
    }

    /**
     * Set the environment for the client
     *
     * @param string $environment
     * @throws SendCloudApiException
     * @deprecated
     */
    public function setEnvironment(string $environment): void
    {
        if ($environment === 'test') {
            throw new SendCloudApiException('SendCloud test environment is no longer available');
        }
    }

    /**
     * Download a resource.
     *
     * @param string|UriInterface $url relative to apiUrl or absolute
     * @param array               $headers
     * @return string
     * @throws SendCloudApiException
     * @throws RuntimeException if unable to read or an error occurs while reading.
     */
    public function download(string $url, array $headers = ['Accept' => 'application/pdf']): string
    {
        try {
            $result = $this->client()->get($url, ['headers' => $headers]);
        } catch (RequestException $e) {
            throw new SendCloudApiException('SendCloud error: ' . $e->getMessage(), $e->getResponse()->getStatusCode());
        }

        return $result->getBody()->getContents();
    }
}

