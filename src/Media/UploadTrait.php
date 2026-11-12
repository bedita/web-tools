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
namespace BEdita\WebTools\Media;

use BEdita\WebTools\ApiClientProvider;
use Cake\Http\Exception\BadRequestException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Use this trait in a controller or component to upload media files.
 * An `UploadedFileInterface` item is required representing a file sent via.
 * It is normally found in requesta data where `<input type="file">` is used.
 * A media object is then created via API client having the file as internal stream.
 */
trait UploadTrait
{
    /**
     * Upload a file and create a media object having the file as internal stream.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The array of file data.
     * @param string $type The media object type to create.
     * @param bool $private Impose a private URL for stream file (default `false`).
     * @return array|null
     * @throws \Cake\Http\Exception\BadRequestException When missing file or type
     */
    protected function uploadMedia(UploadedFileInterface $file, string $type, bool $private = false): ?array
    {
        if (empty($file->getClientFilename()) || empty($type)) {
            throw new BadRequestException(__('Missing file to upload or object type.'));
        }

        /** @var \BEdita\SDK\BEditaClient $apiClient */
        $apiClient = ApiClientProvider::getApiClient();

        $headers = ['Content-Type' => $file->getClientMediaType()];
        $fileTmp = file_get_contents($file->getStream()->getMetadata('uri'));
        $query = $private ? '?private_url=true' : '';

        return $apiClient->post(
            sprintf('/%s/upload/%s%s', $type, $file->getClientFilename(), $query),
            $fileTmp,
            $headers,
        );
    }
}
