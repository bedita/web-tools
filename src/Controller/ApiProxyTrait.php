<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2020 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Controller;

use BEdita\SDK\BEditaClientException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Routing\Router;
use Cake\Utility\Hash;

trait ApiProxyTrait
{
    /**
     * Replace links with
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->viewBuilder()
            ->setClassName('Json')
            ->setOption('serialize', true);
    }

    /**
     * Set base URL used for mask links removing trailing slashes.
     *
     * @param string $path The path on which build base URL
     * @return void
     */
    protected function setBaseUrl($path): void
    {
        $requestPath = $this->request->getPath();
        $basePath = substr($requestPath, 0, strpos($requestPath, $path));
        $this->baseUrl = Router::url(rtrim($basePath, '/'), true);
    }

    /**
     * Proxy for GET requests to BEdita4 API
     *
     * @param string $path The path for API request
     * @return void
     */
    public function get($path = '')
    {
        $this->setBaseUrl($path);
        $this->request([
            'method' => 'get',
            'path' => $path,
            'query' => $this->request->getQueryParams(),
        ]);
    }

    /**
     * Routes a request to the API handling response and errors.
     *
     * `$options` are:
     * - method => the HTTP request method
     * - path => a string representing the complete endpoint path
     * - query => an array of query strings
     * - body => the body sent
     * - headers => an array of headers
     *
     * @param array $options The request options
     * @return void
     */
    protected function request(array $options): void
    {
        $options += [
            'method' => '',
            'path' => '',
            'query' => null,
            'body' => null,
            'headers' => null,
        ];

        try {
            switch (strtolower($options['method'])) {
                case 'get':
                    $response = $this->apiClient->get($options['path'], $options['query'], $options['headers']);
                    break;
                case 'post':
                    $response = $this->apiClient->post($options['path'], $options['body'], $options['headers']);
                    break;
                case 'patch':
                    $response = $this->apiClient->patch($options['path'], $options['body'], $options['headers']);
                    break;
                case 'delete':
                    $response = $this->apiClient->delete($options['path'], $options['body'], $options['headers']);
                    break;
                default:
                    throw new MethodNotAllowedException();
            }

            if (empty($response) || !is_array($response)) {
                return;
            }

            $response = $this->maskResponseLinks($response);
            $this->set($response);
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * Handle error
     *
     * @param \Throwable $error The error thrown.
     * @return void
     */
    protected function handleError(\Throwable $error): void
    {
        $status = $error->getCode();
        if ($status < 100 || $status > 599) {
            $status = 500;
        }
        $this->response = $this->response->withStatus($status);
        $errorData = [
            'status' => $status,
            'title' => $error->getMessage(),
        ];
        $this->set('error', $errorData);

        if (!$error instanceof BEditaClientException) {
            return;
        }

        $errorAttributes = $error->getAttributes();
        if (!empty($errorAttributes)) {
            $this->set('error', $errorAttributes);
        }
    }

    /**
     * Mask links of response to not expose API URL.
     *
     * @param array $response The response from API
     * @return array
     */
    protected function maskResponseLinks(array $response): array
    {
        $response = $this->maskLinks($response, '$id');
        $response = $this->maskLinks($response, 'links');
        $response = $this->maskLinks($response, 'meta.schema');

        if (!empty($response['meta']['resources'])) {
            $response = $this->maskMultiLinks($response, 'meta.resources', 'href');
        }

        $data = Hash::get($response, 'data');
        if (empty($data)) {
            return $response;
        }

        if (Hash::numeric(array_keys($data))) {
            foreach ($data as $key => &$item) {
                $item = $this->maskLinks($item, 'links');
                $item = $this->maskMultiLinks($item);
            }
            $response['data'] = $data;
        } else {
            $response['data']['relationships'] = $this->maskMultiLinks($data);
        }

        return $response;
    }

    /**
     * Mask links across multidimensional array inside an array relationships data.
     *
     * @param array $data The data with relationships and links to mask
     * @param string $path The path to search for
     * @param string $key The key on which are the links
     * @return array
     */
    protected function maskMultiLinks(array $data, $path = 'relationships', $key = 'links'): array
    {
        $relationships = Hash::get($data, $path, []);
        foreach ($relationships as &$rel) {
            $rel = $this->maskLinks($rel, $key);
        }

        return Hash::insert($data, $path, $relationships);
    }

    /**
     * Mask links found in `$path`
     *
     * @param array|string $data The data with links to mask
     * @param string $path The path to search for
     * @return array
     */
    protected function maskLinks($data, $path): array
    {
        $links = Hash::get($data, $path, []);
        if (empty($links)) {
            return $data;
        }

        if (is_string($links)) {
            $links = str_replace($this->apiClient->getApiBaseUrl(), $this->baseUrl, $links);

            return Hash::insert($data, $path, $links);
        }

        foreach ($links as &$link) {
            $link = str_replace($this->apiClient->getApiBaseUrl(), $this->baseUrl, $link);
        }

        return Hash::insert($data, $path, $links);
    }
}
