<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2021 Atlas Srl, ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\Controller\Component;

use BEdita\WebTools\ApiClientProvider;
use Cake\Cache\Cache;
use Cake\Controller\Component;

/**
 * Component to cache some GET API calls.
 *
 * An index is used in order to refresh this cache with an external script.
 */
class ApiCacheComponent extends Component
{
    /**
     * Default config for this component.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'cache' => '_apicache_',
    ];

    /**
     * Cache index
     *
     * @var array
     */
    protected array $cacheIndex = [];

    /**
     * Use 'default' as fallback if no cache configuration is found.
     *
     * @param array $config The configuration settings provided to this helper.
     * @return void
     */
    public function initialize(array $config): void
    {
        if (Cache::getConfig($this->getConfig('cache')) === null) {
            $this->setConfig('cache', 'default');
        }
    }

    /**
     * Cache key using type and query string
     *
     * @param string $path API path
     * @param array|null $query Optional query string
     * @return string
     */
    protected function cacheKey(string $path, ?array $query = null): string
    {
        $hash = md5(json_encode($query));
        $path = str_replace('/', '_', $path);

        return sprintf('%s_%s', $path, $hash);
    }

    /**
     * Update cache index
     *
     * @param string $key Cache Key
     * @param array $params Cache params
     * @return void
     */
    protected function updateCacheIndex(string $key, array $params): void
    {
        if (!empty($this->cacheIndex[$key])) {
            return;
        }
        $this->cacheIndex[$key] = $params;
        Cache::write('index', $this->cacheIndex, $this->getConfig('cache'));
    }

    /**
     * Read current cache index
     *
     * @return void
     */
    protected function readIndex(): void
    {
        if (!empty($this->cacheIndex)) {
            return;
        }
        $this->cacheIndex = array_filter((array)Cache::read('index', $this->getConfig('cache')));
    }

    /**
     * Cached GET API call
     *
     * @param string $path Path invoked
     * @param array|null $query Optional query string
     * @return array
     */
    public function get(string $path, ?array $query = null): array
    {
        $this->readIndex();
        $key = $this->cacheKey($path, $query);

        $response = (array)Cache::remember(
            $key,
            function () use ($key, $path, $query) {
                return (array)ApiClientProvider::getApiClient()->get($path, $query);
            },
            $this->getConfig('cache')
        );
        $this->updateCacheIndex($key, compact('path', 'query'));

        return $response;
    }
}
