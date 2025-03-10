<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\View\Helper;

use BEdita\WebTools\ApiClientProvider;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * Helper to obtain thumbnail url
 */
class ThumbHelper extends Helper
{
    use LogTrait;

    /**
     * Default config for this helper.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'cache' => '_thumbs_',
    ];

    /**
     * @var int Thumb not available
     */
    public const NOT_AVAILABLE = -10;

    /**
     * @var int Thumb not ready
     */
    public const NOT_READY = -20;

    /**
     * @var int Thumb not acceptable
     */
    public const NOT_ACCEPTABLE = -30;

    /**
     * @var int Thumb has no url
     */
    public const NO_URL = -40;

    /**
     * @var int Thumb is OK
     */
    public const OK = 1;

    /**
     * Use 'default' as fallback if no cache configuration is found.
     *
     * @param array $config The configuration settings provided to this helper.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        if (!empty($config['cache'])) {
            $this->setConfig('cache', $config['cache']);
        }
        $cacheCfg = $this->getConfig('cache');
        $cfg = Cache::getConfig($cacheCfg);
        if ($cfg === null) {
            $this->setConfig('cache', 'default');
        }
    }

    /**
     * Verify status of image thumb.
     * Return int representing status.
     * Possible values:
     *
     *   NOT_AVAILABLE: something went wrong during api call
     *   NOT_READY: thumb is available, but not ready
     *   NOT_ACCEPTABLE: image is not acceptable, api won't create thumb
     *   NO_URL: url not present in api response
     *   OK: thumb available, ready and with a proper url
     *
     * @param int|string $imageId The image ID
     * @param array|null $options The thumbs options
     * @param string|null $url The thumb url to populate when static::OK
     * @return int|null
     */
    public function status($imageId, ?array $options = ['preset' => 'default'], &$url = ''): ?int
    {
        if (empty($imageId) && empty($options['ids'])) {
            return static::NOT_ACCEPTABLE;
        }
        try {
            $apiClient = ApiClientProvider::getApiClient();
            $response = $apiClient->thumbs(intval($imageId), $options);
            if (empty($response['meta']['thumbnails'][0])) {
                return static::NOT_AVAILABLE;
            }
            $thumb = $response['meta']['thumbnails'][0];
            // check thumb is acceptable
            if (!$this->isAcceptable($thumb)) {
                return static::NOT_ACCEPTABLE;
            }
            // check thumb is ready
            if (!$this->isReady($thumb)) {
                return static::NOT_READY;
            }
            // check thumb has url
            if (!$this->hasUrl($thumb)) {
                return static::NO_URL;
            }
            $url = $thumb['url'];
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'error');

            return static::NOT_AVAILABLE;
        }

        return static::OK;
    }

    /**
     * Obtain thumbnail using API thumbs.
     *
     * @param int $imageId The image ID.
     * @param array|null $options The thumbs options.
     * @return string|int The url if available, the status code otherwise (see Thumb constants).
     */
    public function url($imageId, $options)
    {
        $url = null;
        $status = $this->status($imageId, $options, $url);
        if ($status === static::OK) {
            return $url;
        }

        return $status;
    }

    /**
     * Verify if thumb is acceptable
     *
     * @param array $thumb The thumbnail data
     * @return bool the acceptable flag
     */
    private function isAcceptable($thumb = []): bool
    {
        if (isset($thumb['acceptable']) && $thumb['acceptable'] === false) {
            return false;
        }

        return true;
    }

    /**
     * Verify if thumb is ready
     *
     * @param array $thumb The thumbnail data
     * @return bool the ready flag
     */
    private function isReady($thumb = []): bool
    {
        if (!empty($thumb['ready']) && $thumb['ready'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Verify if thumb has url
     *
     * @param array $thumb The thumbnail data
     * @return bool the url availability
     */
    private function hasUrl($thumb = []): bool
    {
        if (!empty($thumb['url'])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve thumb URL using cache.
     * Silently fail with log if no image 'id' is found in array.
     *
     * @param array|null $image Image object array containing at least `id`
     * @param string $options Thumb options
     * @return string
     */
    public function getUrl(?array $image, array $options = []): string
    {
        if (empty($image) || empty($image['id'])) {
            $this->log(sprintf('Missing image ID - %s', json_encode($image)), 'warning');

            return '';
        }
        $thumbHash = md5((string)Hash::get($image, 'meta.media_url') . json_encode($options));
        $key = sprintf('%d_%s', $image['id'], $thumbHash);

        return (string)Cache::remember(
            $key,
            function () use ($image, $options) {
                return $this->url($image['id'], $options);
            },
            $this->getConfig('cache')
        );
    }
}
