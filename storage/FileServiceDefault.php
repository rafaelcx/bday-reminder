<?php

declare(strict_types=1);

namespace App\Storage;

class FileServiceDefault implements FileService {

    private string $file_location;

    public function __construct(string $file_location) {
        $this->file_location = $file_location;
    }

    public function getFileContents(string $file_name): string {
        $file_path = $this->file_location . $file_name;

        if (!file_exists($file_path)) {
            return '';
        }
        return file_get_contents($file_path);
    }

    public function putFileContents(string $file_name, string $contents): void {
        $file_path = $this->file_location . $file_name;
        file_put_contents($file_path, $contents);
    }

}
