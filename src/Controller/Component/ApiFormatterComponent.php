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

use Cake\Collection\Collection;
use Cake\Controller\Component;
use Cake\Utility\Hash;

/**
 * Component class to format API response data.
 */
class ApiFormatterComponent extends Component
{
    /**
     * Embed included data into relationships.
     *
     * @param array $response The response from API
     * @return array
     */
    public function embedIncluded(array $response): array
    {
        $data = (array)Hash::get($response, 'data');
        if (empty($data)) {
            return $response;
        }

        $included = (array)Hash::get($response, 'included');
        if (empty($included)) {
            return $response;
        }

        $included = collection($included);
        if (!Hash::numeric(array_keys($data))) {
            $response['data'] = $this->addIncluded($data, $included);

            return $response;
        }

        foreach ($data as &$d) {
            $d = $this->addIncluded($d, $included);
        }
        unset($d);

        $response['data'] = $data;

        return $response;
    }

    /**
     * Add included data to main resource.
     *
     * @param array $resource The resource.
     * @param \Cake\Collection\Collection $included The included collection.
     * @return array
     */
    protected function addIncluded(array $resource, Collection $included): array
    {
        foreach ($resource['relationships'] as &$relation) {
            if (empty($relation['data'])) {
                continue;
            }

            $relation['data'] = $this->extractFromIncluded($included, (array)$relation['data']);
        }
        unset($relation);

        return $resource;
    }

    /**
     * Extract items from included starting from $relationship data.
     *
     * @param \Cake\Collection\Collection $included The included collection
     * @param array $relationshipData Array of relationship data.
     *                                Every item must contain 'type' and 'id'.
     * @return array
     */
    protected function extractFromIncluded(Collection $included, array $relationshipData): array
    {
        // case is 1-1 relationship - object relation in translations is a special case
        if (array_key_exists('id', $relationshipData)) {
            return (array)$included->firstMatch([
                'type' => $relationshipData['type'],
                'id' => $relationshipData['id'],
            ]);
        }
        foreach ($relationshipData as &$data) {
            $data = (array)$included->firstMatch([
                'type' => $data['type'],
                'id' => $data['id'],
            ]);
        }
        unset($data);

        return $relationshipData;
    }

    /**
     * Replace a translation in main objects.
     * It must be used after `included` data have been embedded using `self::embedIncluded()`.
     *
     * @param array $response The response API array
     * @param string $lang The lang to search in translations
     * @return array
     */
    public function replaceWithTranslation(array $response, string $lang): array
    {
        if (empty($response['data'])) {
            return $response;
        }

        $data = (array)Hash::get($response, 'data');

        if (!Hash::numeric(array_keys($data))) {
            $response['data']['attributes'] = array_merge($response['data']['attributes'], $this->extractTranslatedFields($data, $lang));

            return $response;
        }

        foreach ($response['data'] as &$d) {
            $d['attributes'] = array_merge($d['attributes'], $this->extractTranslatedFields($d, $lang));
        }

        return $response;
    }

    /**
     * Extract translated fields for a language.
     *
     * @param array $data The object data
     * @param string $lang The lang to extract
     * @return array
     */
    protected function extractTranslatedFields(array $data, string $lang): array
    {
        $path = sprintf('relationships.translations.data.{n}.attributes[lang=%s].translated_fields', $lang);
        if ($lang === Hash::get($data, 'attributes.lang')) {
            return [];
        }

        return array_filter(current(Hash::extract($data, $path)));
    }
}
