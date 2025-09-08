<?php

declare(strict_types=1);

namespace Test\Storage;

use App\Storage\FileServiceDefault;
use Test\CustomTestCase;

class FileServiceDefaultTest extends CustomTestCase {

    private const FILE_LOCATION = __DIR__ . '/';
    private const FILE_NAME = 'test-file.json';
    
    /** 
     * @after
     */
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
        $file_content = 'old_content';
        file_put_contents(self::FILE_LOCATION . self::FILE_NAME, $file_content);

        $new_content = 'new_content';
        $service = new FileServiceDefault(self::FILE_LOCATION);
        $service->putFileContents(self::FILE_NAME, $new_content);

        $file_state = $service->getFileContents(self::FILE_NAME);
        $this->assertSame($new_content, $file_state);
    }

    public function testService_PutFileContents_WhenFileDoesNotExist(): void {
        $content = 'more_content';

        $service = new FileServiceDefault(self::FILE_LOCATION);
        $service->putFileContents(self::FILE_NAME, $content);

        $file_state = $service->getFileContents(self::FILE_NAME);
        $this->assertSame($content, $file_state);
    }

}
