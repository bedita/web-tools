<?php
declare(strict_types=1);

/**
 * API Formatter
 *
 * Copyright 2021 Atlas Srl
 */
namespace BEdita\WebTools\Controller\Component;

use Cake\Collection\Collection;
use Cake\Controller\Component;
use Cake\I18n\Number;
use Cake\Utility\Hash;

/**
 * Component class to format API response data.
 */
class ApiFormatterComponent extends Component
{
    /**
     * Add 'stream' to each object in response data, when available in included data.
     *
     * @param array $response The response data
     * @return array
     */
    public function addObjectsStream(array $response): array
    {
        $objects = (array)Hash::get($response, 'data');
        if (empty($objects)) {
            return $response;
        }
        $included = (array)Hash::get($response, 'included');
        if (empty($included)) {
            return $response;
        }
        $included = collection($included);
        /** @var array $object */
        foreach ($objects as &$object) {
            if (!Hash::check($object, 'relationships.streams.data')) {
                continue;
            }
            $relationData = (array)Hash::get($object, 'relationships.streams.data');
            $streams = $this->extractFromIncluded($included, $relationData);
            $stream = $streams[0];
            if (Hash::check($stream, 'meta.file_size')) {
                $size = (int)Hash::get($stream, 'meta.file_size');
                $stream['meta']['file_size'] = Number::toReadableSize($size);
            }
            $object['stream'] = $stream;
        }
        $response['data'] = $objects;

        return $response;
    }

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
            return $data;
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
        foreach ($relationshipData as &$data) {
            $data = (array)$included->firstMatch([
                'type' => $data['type'],
                'id' => $data['id'],
            ]);
        }
        unset($data);

        return $relationshipData;
    }
}
