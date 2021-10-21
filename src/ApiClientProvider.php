<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools;

use BEdita\SDK\BEditaClient;
use Cake\Core\Configure;

/**
 * BEdita4 API client provider singleton class.
 */
class ApiClientProvider
{
    use SingletonTrait;

    /**
     * BEdita4 API client
     *
     * @var \BEdita\SDK\BEditaClient
     */
    private $apiClient = null;

    /**
     * Read singleton API client data.
     * In `$options` you may provide a log configuration via `Log` key setting a log file.
     * Example:
     * ```
     *   [
     *     'Log' => [
     *       'log_file' => LOGS . 'api.log',
     *     ],
     *   ]
     * ```
     *
     * @param array $options Client options
     * @return \BEdita\SDK\BEditaClient
     */
    public static function getApiClient(array $options = []): BEditaClient
    {
        if (static::getInstance()->apiClient) {
            $logOptions = !empty($options['Log']) ? $options['Log'] : Configure::read('API.log');
            if (!empty($logOptions)) {
                static::getInstance()->apiClient->initLogger($logOptions);
            }

            return static::getInstance()->apiClient;
        }

        return static::getInstance()->createClient($options);
    }

    /**
     * Create new default API client.
     *
     * @param mixed $options Client options
     * @return \BEdita\SDK\BEditaClient
     */
    private function createClient(array $options = []): BEditaClient
    {
        $this->apiClient = new BEditaClient(Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey'), [], Configure::read('API.guzzleConfig', []));
        $logOptions = !empty($options['Log']) ? $options['Log'] : Configure::read('API.log');
        if (!empty($logOptions)) {
            $this->apiClient->initLogger($logOptions);
        }

        return $this->apiClient;
    }

    /**
     * Set a new API client.
     *
     * @param \BEdita\SDK\BEditaClient|null $client New API client to set
     * @return void
     */
    public static function setApiClient(?BEditaClient $client): void
    {
        static::getInstance()->apiClient = $client;
    }
}
