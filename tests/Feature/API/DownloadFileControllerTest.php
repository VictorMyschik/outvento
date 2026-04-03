<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Support\Facades\Storage;

final class DownloadFileControllerTest extends ApiTestCase
{
    private const string DOWNLOAD_ENDPOINT = '/download';

    public function testDownloadFileWithValidParams(): void
    {
        $diskName = 'public';
        $fileName = 'test-file.txt';
        $fileContent = 'Test file content';

        Storage::disk($diskName)->put($fileName, $fileContent);

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => $diskName,
            'file' => $fileName,
            'name' => 'downloaded-file.txt',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Disposition');

        // Cleanup
        Storage::disk($diskName)->delete($fileName);
    }

    public function testDownloadFileUsingDefaultPublicDisk(): void
    {
        $fileName = 'default-disk-file.txt';
        $fileContent = 'Content for default disk';

        Storage::disk('public')->put($fileName, $fileContent);

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'file' => $fileName,
            'name' => 'downloaded.txt',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Disposition');

        Storage::disk('public')->delete($fileName);
    }

    public function testDownloadFileWithoutName(): void
    {
        $fileName = 'file-without-name.txt';
        Storage::disk('public')->put($fileName, 'content');

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => 'public',
            'file' => $fileName,
        ]);

        $response->assertOk();

        Storage::disk('public')->delete($fileName);
    }

    public function testDownloadNonExistentFileReturns404(): void
    {
        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => 'public',
            'file' => 'non-existent-file.txt',
            'name' => 'download.txt',
        ]);

        $response->assertStatus(404);
    }

    public function testDownloadFromSpecificDisk(): void
    {
        $fileName = 'users-disk-file.txt';
        $fileContent = 'User specific content';

        if (Storage::disk('users')->exists($fileName)) {
            Storage::disk('users')->delete($fileName);
        }

        Storage::disk('users')->put($fileName, $fileContent);

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => 'users',
            'file' => $fileName,
            'name' => 'user-file.txt',
        ]);

        $response->assertOk();

        Storage::disk('users')->delete($fileName);
    }

    public function testDownloadFileWithSpecialCharactersInName(): void
    {
        $fileName = 'file.txt';
        $downloadName = 'файл-文件-αρχείο.txt';

        Storage::disk('public')->put($fileName, 'content');

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => 'public',
            'file' => $fileName,
            'name' => $downloadName,
        ]);

        $response->assertOk();

        Storage::disk('public')->delete($fileName);
    }

    public function testDownloadLargeFile(): void
    {
        $fileName = 'large-file.bin';
        $largeContent = str_repeat('x', 10 * 1024 * 1024); // 10MB

        Storage::disk('public')->put($fileName, $largeContent);

        $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
            'disk' => 'public',
            'file' => $fileName,
            'name' => 'large.bin',
        ]);

        $response->assertOk();

        Storage::disk('public')->delete($fileName);
    }

    public function testDownloadFileWithDifferentMimeTypes(): void
    {
        $files = [
            'text.txt'  => ['content' => 'text content', 'mime' => 'text/plain'],
            'data.json' => ['content' => '{"key":"value"}', 'mime' => 'application/json'],
        ];

        foreach ($files as $fileName => $fileData) {
            Storage::disk('public')->put($fileName, $fileData['content']);

            $response = $this->request('GET', self::DOWNLOAD_ENDPOINT, [
                'disk' => 'public',
                'file' => $fileName,
                'name' => $fileName,
            ]);

            $response->assertOk();

            Storage::disk('public')->delete($fileName);
        }
    }
}

