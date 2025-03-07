<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2025 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\Http;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Log\LogTrait;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Base class for clients.
 */
abstract class BaseClient
{
    use InstanceConfigTrait;
    use LogTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'auth' => null,
        'logLevel' => 'error',
        'url' => null,
    ];

    /**
     * The HTTP client.
     *
     * @var \Cake\Http\Client
     */
    protected Client $client;

    /**
     * Constructor. Initialize HTTP client.
     *
     * @param array $config The configuration
     */
    public function __construct(array $config = [])
    {
        $config += (array)Configure::read($this->defaultConfigName(), []);
        $this->setConfig($config);
        $this->validateConf($this->getValidator());
        $this->createClient();
    }

    /**
     * Get default config name.
     * It's the name of the client class without `Client` suffix.
     *
     * @return string
     */
    protected function defaultConfigName(): string
    {
        $shortName = App::shortName(static::class, 'Http', 'Client');
        [, $name] = pluginSplit($shortName);

        return $name;
    }

    /**
     * Return the Validator object
     *
     * @return \Cake\Validation\Validator
     */
    protected function getValidator(): Validator
    {
        $validator = new Validator();

        return $validator
            ->requirePresence('url')
            ->notEmptyString('url');
    }

    /**
     * Validate configuration data.
     *
     * @param \Cake\Validation\Validator $validator The validator object
     * @return void
     */
    protected function validateConf(Validator $validator): void
    {
        $errors = $validator->validate($this->getConfig());
        if (!empty($errors)) {
            throw new InvalidArgumentException(
                sprintf('%s client config not valid: %s', static::class, json_encode($errors))
            );
        }
    }

    /**
     * Create JSON HTTP client.
     *
     * @return void
     */
    protected function createClient(): void
    {
        $parsedUrl = parse_url($this->getConfig('url'));
        $options = [
            'host' => $parsedUrl['host'],
            'scheme' => $parsedUrl['scheme'],
            'path' => $parsedUrl['path'] ?? '',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ] + $this->getConfig();
        $this->client = new Client($options);
    }

    /**
     * Get the correct relative url.
     *
     * @param string $url The relative url
     * @return string
     */
    protected function getUrl(string $url): string
    {
        if (strpos($url, 'https://') === 0) {
            return $url;
        }
        $base = trim($this->client->getConfig('path'), '/');
        $url = trim($url, '/');

        return sprintf('%s/%s', $base, $url);
    }

    /**
     * Log API call.
     *
     * @param string $call The API call
     * @param string $url The API url
     * @param string $payload The json payload
     * @param \Cake\Http\Client\Response $response The API response
     * @return ?string
     */
    protected function logCall(string $call, string $url, string $payload, Response $response): ?string
    {
        $level = $this->getConfig('logLevel') ?? 'error';
        if (!in_array($level, ['error', 'debug'])) {
            return null;
        }
        $result = $response->isOk() ? '' : 'error';
        if ($level === 'error' && empty($result)) {
            return null;
        }
        $level = $result === 'error' ? 'error' : $level;
        $message = sprintf(
            '%s API %s | %s %s | with status %s: %s - Payload: %s',
            $response->isOk() ? '[OK]' : '[ERROR]',
            $this->defaultConfigName(),
            $call,
            $url,
            $response->getStatusCode(),
            (string)$response->getBody(),
            $payload
        );
        $message = trim($message);
        $this->log($message, $level);

        return $message;
    }

    /**
     * Get http client
     *
     * @return \Cake\Http\Client The client
     */
    public function getHttpClient(): Client
    {
        return $this->client;
    }

    /**
     * Get request.
     *
     * @param string $url The request url
     * @param array $data The query data
     * @param array $options Request options
     * @return \Cake\Http\Client\Response
     */
    public function get(string $url, array $data = [], array $options = []): Response
    {
        $apiUrl = $this->getUrl($url);
        $response = $this->client->get($apiUrl, $data, $options);
        $this->logCall('/GET', $apiUrl, json_encode($data), $response);

        return $response;
    }

    /**
     * Post request.
     *
     * @param string $url The request url
     * @param array $data The post data
     * @param array $options Request options
     * @return \Cake\Http\Client\Response
     */
    public function post(string $url, array $data = [], array $options = []): Response
    {
        $data = json_encode($data);
        $apiUrl = $this->getUrl($url);
        $response = $this->client->post($apiUrl, $data, $options);
        $this->logCall('/POST', $apiUrl, $data, $response);

        return $response;
    }

    /**
     * Patch request.
     *
     * @param string $url The request url
     * @param array $data The post data
     * @param array $options Request options
     * @return \Cake\Http\Client\Response
     */
    public function patch(string $url, array $data = [], array $options = []): Response
    {
        $apiUrl = $this->getUrl($url);
        $data = json_encode($data);
        $response = $this->client->patch($apiUrl, $data, $options);
        $this->logCall('/PATCH', $apiUrl, $data, $response);

        return $response;
    }

    /**
     * Put request.
     *
     * @param string $url The request url
     * @param array $data The post data
     * @param array $options Request options
     * @return \Cake\Http\Client\Response
     */
    public function put(string $url, array $data = [], array $options = []): Response
    {
        $apiUrl = $this->getUrl($url);
        $data = json_encode($data);
        $response = $this->client->put($this->getUrl($url), $data, $options);
        $this->logCall('/PUT', $apiUrl, $data, $response);

        return $response;
    }

    /**
     * Delete request.
     *
     * @param string $url The request url
     * @param array $data The post data
     * @param array $options Request options
     * @return \Cake\Http\Client\Response
     */
    public function delete(string $url, array $data = [], array $options = []): Response
    {
        $apiUrl = $this->getUrl($url);
        $data = json_encode($data);
        $response = $this->client->delete($this->getUrl($url), $data, $options);
        $this->logCall('/DELETE', $apiUrl, $data, $response);

        return $response;
    }
}
