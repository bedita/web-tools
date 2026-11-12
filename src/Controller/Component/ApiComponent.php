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
namespace BEdita\WebTools\Controller\Component;

use BEdita\SDK\BEditaClient;
use Cake\Controller\Component;

/**
 * Proxy component for BEdita API.
 *
 * @method void setupTokens(array $tokens)
 * @method array getDefaultHeaders()
 * @method string getApiBaseUrl()
 * @method array getTokens()
 * @method \Psr\Http\Message\ResponseInterface|null getResponse()
 * @method int|null getStatusCode()
 * @method string|null getStatusMessage()
 * @method array|null getResponseBody()
 * @method array|null authenticate(string $username, string $password)
 * @method array|null get(string $path, ?array $query = null, ?array $headers = null)
 * @method array|null getObjects(string $type = 'objects', ?array $query = null, ?array $headers = null)
 * @method array|null getObject($id, string $type = 'objects', ?array $query = null, ?array $headers = null)
 * @method array|null getRelated($id, string $type, string $relation, ?array $query = null, ?array $headers = null)
 * @method array|null addRelated($id, string $type, string $relation, array $data, ?array $headers = null)
 * @method array|null removeRelated($id, string $type, string $relation, array $data, ?array $headers = null)
 * @method array|null replaceRelated($id, string $type, string $relation, array $data, ?array $headers = null)
 * @method array|null save(string $type, array $data, ?array $headers = null)
 * @method array|null deleteObject($id, string $type)
 * @method array|null remove($id)
 * @method array|null upload(string $filename, string $filepath, ?array $headers = null)
 * @method array|null createMediaFromStream($streamId, string $type, array $body)
 * @method array|null thumbs($id = null, $query = [])
 * @method array|null schema(string $type)
 * @method array|null relationData(string $name)
 * @method array|null restoreObject($id, string $type)
 * @method array|null patch(string $path, $body, ?array $headers = null)
 * @method array|null post(string $path, $body, ?array $headers = null)
 * @method array|null delete(string $path, $body = null, ?array $headers = null)
 * @method void refreshTokens()
 */
class ApiComponent extends Component
{
    /**
     * Default component configuration.
     *
     * - apiClient => the BEditaClient instance
     *
     * @var array
     */
    protected $_defaultConfig = [
        'apiClient' => null,
        'exceptionsEnabled' => true,
    ];

    /**
     * Keep last response error
     *
     * @var \Throwable
     */
    protected $error = null;

    /**
     * Return the instance of BEditaClient
     *
     * @return \BEdita\SDK\BEditaClient
     */
    public function getClient(): BEditaClient
    {
        $client = $this->getConfigOrFail('apiClient');
        if (!$client instanceof BEditaClient) {
            throw new \InvalidArgumentException(__('Not a valid api client class'));
        }

        return $client;
    }

    /**
     * Set if the client exceptions will be thrown.
     *
     * @param bool $value The value to set
     * @return $this
     */
    public function enableExceptions(bool $value)
    {
        $this->setConfig('exceptionsEnabled', $value);

        return $this;
    }

    /**
     * Say if there was error in the last API request.
     * Note that error is set if `exceptionsEnabled` conf is `false`
     *
     * @return array
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Get the last API error.
     * Note that error is set if `exceptionsEnabled` conf is `false`
     *
     * @return \Throwable|null
     */
    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    /**
     * Proxy to BEditaClient methods.
     *
     * @param string $name The method invoked
     * @param array $arguments The arguments for the method
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        $response = null;
        $this->error = null;
        try {
            $response = call_user_func_array([$this->getClient(), $name], $arguments);
        } catch (\Throwable $e) {
            if ($this->getConfig('exceptionsEnabled') === true) {
                throw $e;
            }
            $this->log($e->getMessage(), 'error');

            $this->error = $e;
        }

        return $response;
    }
}
