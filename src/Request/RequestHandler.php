<?php
/**
 * Clarifai Library
 *
 * @category Library
 * @package  Clarifai
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/clarifai-php/blob/master/LICENSE>
 * @link     https://github.com/darrynten/clarifai-php
 */

namespace DarrynTen\Clarifai\Request;

use DarrynTen\Clarifai;
use DarrynTen\Clarifai\Exception\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * RequestHandler Class
 *
 * @category Library
 * @package  Clarifai
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/clarifai-php/blob/master/LICENSE>
 * @link     https://github.com/darrynten/clarifai-php
 */
class RequestHandler
{
    /**
     * GuzzleHttp Client
     *
     * @var Client $client
     */
    private $client;

    /**
     * The Clarifai url
     *
     * @var string $url
     */
    private $url = 'https://api.clarifai.com';

    /**
     * The version of the Clarifai API
     *
     * @var string $version
     */
    private $version = 'v2';

    /**
     * Clarifai API key
     *
     * @var string $apiKey
     */
    private $apiKey;


    /**
     * Request handler constructor
     *
     * @param string $apiKey The API key
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->tokenExpireTime = new \DateTime();
        $this->client = new Client();
    }

    /**
     * Makes a request using Guzzle
     *
     * @param string $method The HTTP request method (GET/POST/etc)
     * @param string $uri The resource
     * @param array $options Request options
     * @param array $parameters Request parameters
     *
     * @see RequestHandler::request()
     *
     * @return array
     * @throws ApiException
     */
    public function handleRequest(string $method, string $uri = '', array $options = [], array $parameters = [])
    {
        // Are we going a GET or a POST?
        if (!empty($parameters)) {
            if ($method === 'GET') {
                // Send as get params
                $options['query'] = $parameters;
            }
            if ($method === 'POST' || $method === 'PATCH' || $method === 'DELETE') {
                // Otherwise send JSON in the body
                $options['json'] = (object)$parameters;
            }
        }

        // Let's go
        try {
            $response = $this->client->request($method, $uri, $options);

            // All good
            return json_decode($response->getBody(), true);
        } catch (RequestException $exception) {
            $message = $exception->getMessage();

            throw new ApiException($message, $exception->getCode(), $exception);
        }
    }

    /**
     * Get token for Clarifai API requests
     *
     * @return string
     */
    private function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Makes a request to Clarifai
     *
     * @param string $method The API method
     * @param string $path The path
     * @param array $parameters The request parameters
     *
     * @return []
     *
     * @throws ApiException
     */
    public function request(string $method, string $path, array $parameters = [])
    {
        $options = [
            'headers' => [
                'Authorization' => sprintf('Key %s', $this->getApiKey()),
                'User-Agent' => sprintf(
                  'Clarifai PHP (https://github.com/bigstylee/clarifai-php);v%s;%s',
                  \DarrynTen\Clarifai\Clarifai::VERSION,
                  phpversion()
                ),
            ],
        ];

        // TODO check for batch operation
        return $this->handleRequest(
            $method,
            sprintf('%s/%s/%s', $this->url, $this->version, $path),
            $options,
            $parameters
        );
    }
}
