<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2021 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\Media;

use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Media\UploadTrait;
use Cake\Http\Exception\BadRequestException;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Message\UploadedFileInterface;

/**
 * {@see \BEdita\WebTools\Media\UploadTrait} Test Case
 */
#[CoversClass(UploadTrait::class)]
#[CoversMethod(UploadTrait::class, 'uploadMedia')]
class UploadTraitTest extends TestCase
{
    use UploadTrait;

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        ApiClientProvider::setApiClient(null);
    }

    /**
     * Invoke trait method
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The array of file data.
     * @param string $type The media object type to create.
     * @param bool $private Impose a private URL for stream file (default `false`).
     * @return array|null
     */
    public function upload(UploadedFileInterface $file, string $type, bool $private = false): ?array
    {
        return $this->uploadMedia($file, $type, $private);
    }

    /**
     * Test `uploadMedia()` method
     *
     * @return void
     */
    public function testUpload(): void
    {
        $path = sprintf('%s/tests/files/test.png', getcwd());
        $resource = fopen($path, 'r');

        $file = new UploadedFile(
            $resource,
            filesize($path),
            UPLOAD_ERR_OK,
            'test.png',
            mime_content_type($resource)
        );

        $expected = [
            'data' => [
                'id' => '9000',
                'type' => 'images',
            ],
        ];
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->onlyMethods(['post'])
            ->getMock();
        $apiMockClient->method('post')->willReturn($expected);
        ApiClientProvider::setApiClient($apiMockClient);

        $result = $this->upload($file, 'images', true);
        static::assertEquals($expected, $result);
    }

    /**
     * Test `uploadMedia()` failure
     *
     * @return void
     */
    public function testFailUpload(): void
    {
        $path = sprintf('%s/tests/files/test.png', getcwd());
        $file = new UploadedFile($path, filesize($path), UPLOAD_ERR_OK);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Missing file to upload or object type');
        $this->upload($file, 'images', true);
    }
}
