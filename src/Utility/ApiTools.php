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
namespace BEdita\WebTools\Utility;

use Cake\Utility\Hash;

/**
 * Api utility methods
 */
class ApiTools
{
    /**
     * Remove attributes from response.
     *
     * @param array $response The response from api client
     * @param array $keysToRemove The keys to remove
     * @return array
     */
    public static function removeAttributes(array $response, array $keysToRemove): array
    {
        // response.data is an array representing a single entity
        if (isset($response['data']['attributes'])) {
            $response['data']['attributes'] = array_diff_key($response['data']['attributes'], array_flip($keysToRemove));

            // remove attributes from included entities
            if (isset($response['included'])) {
                foreach ($response['included'] as $key => $entity) {
                    $response['included'][$key]['attributes'] = array_diff_key($entity['attributes'], array_flip($keysToRemove));
                }
            }

            return $response;
        }
        // response.data is a list of entities
        if (isset($response['data'])) {
            foreach ($response['data'] as $key => $entity) {
                $response['data'][$key]['attributes'] = array_diff_key($entity['attributes'], array_flip($keysToRemove));
            }

            // remove attributes from included entities
            if (isset($response['included'])) {
                foreach ($response['included'] as $key => $entity) {
                    $response['included'][$key]['attributes'] = array_diff_key($entity['attributes'], array_flip($keysToRemove));
                }
            }

            return $response;
        }

        return $response;
    }

    /**
     * Remove included from response.
     *
     * @param array $response The response from api client
     * @return array
     */
    public static function removeIncluded(array $response): array
    {
        return (array)Hash::remove($response, 'included');
    }

    /**
     * Remove `links` from response (recursively).
     *
     * @param array $response The response from api client
     * @return array
     */
    public static function removeLinks(array $response): array
    {
        $response = (array)Hash::remove($response, 'links');

        return self::recursiveRemoveKey($response, 'links');
    }

    /**
     * Remove `relationships` from response (recursively).
     *
     * @param array $response The response from api client
     * @return array
     */
    public static function removeRelationships(array $response): array
    {
        return self::recursiveRemoveKey($response, 'relationships');
    }

    /**
     * Remove `schema` from response.
     *
     * @param array $response The response from api client
     * @return array
     */
    public static function removeSchema(array $response): array
    {
        return (array)Hash::remove($response, 'meta.schema');
    }

    /**
     * Remove a key in an array recursively.
     *
     * @param array $data The starting data
     * @param string $key The key to remove
     * @return array
     */
    public static function recursiveRemoveKey(array $data, string $key): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = self::recursiveRemoveKey($v, $key);
            }
        }

        return array_filter(
            $data,
            function ($k) use ($key) {
                return $k !== $key;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Clean response.
     *
     * @param array $response The response.
     * @param array $options The options to clean.
     * @return array
     */
    public static function cleanResponse(
        array $response,
        array $options = ['included', 'links', 'schema', 'relationships', 'attributes' => []]
    ): array {
        foreach ($options as $key => $option) {
            if (is_string($key) && is_array($option)) {
                $method = 'remove' . ucfirst($key);
                $response = self::$method($response, $option);
                continue;
            }
            $method = 'remove' . ucfirst($option);
            $response = self::$method($response);
        }

        return $response;
    }
}
