<?php

declare(strict_types=1);

namespace Test\Storage;

use App\Storage\FileServiceDefault;
use Test\CustomTestCase;

class FileServiceDefaultTest extends CustomTestCase {

    private const FILE_LOCATION = __DIR__ . '/';
    private const FILE_NAME = 'test-file.json';
    
    /** @after */
    public function cleanTestFiles(): void {
        if (file_exists(self::FILE_LOCATION . self::FILE_NAME)) {
            unlink(self::FILE_LOCATION . self::FILE_NAME);
        }
    }
    
    public function testService_GetFileContents_OnExistingFile(): void {
        $file_content = 'content';
        file_put_contents(self::FILE_LOCATION . self::FILE_NAME, $file_content);

        $service = new FileServiceDefault(self::FILE_LOCATION);
        $result = $service->getFileContents(self::FILE_NAME);
        $this->assertSame($file_content, $result);
    }

    public function testService_GetFileContents_WhenFileDoesNotExist(): void {
        $service = new FileServiceDefault(self::FILE_LOCATION);
        $result = $service->getFileContents(self::FILE_NAME);
        $this->assertSame('', $result);
    }

    public function testService_PutFileContents_OnExistingFile(): void {
        $file_content = 'content';
        file_put_contents(self::FILE_LOCATION . self::FILE_NAME, $file_content);

        $new_contents = 'more_content';
        $service = new FileServiceDefault(self::FILE_LOCATION);
        $service->putFileContents(self::FILE_NAME, $new_contents);

        $file_state = $service->getFileContents(self::FILE_NAME);
        $this->assertSame($new_contents, $file_state);
    }

    public function testSergice_PutFileContents_WhenFileDoesNotExist(): void {
        $contents = 'more_content';

        $service = new FileServiceDefault(self::FILE_LOCATION);
        $service->putFileContents(self::FILE_NAME, $contents);

        $file_state = $service->getFileContents(self::FILE_NAME);
        $this->assertSame($contents, $file_state);
    }

}
