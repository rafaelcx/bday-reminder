<?php

declare(strict_types=1);

namespace App\Storage;

interface FileService {

    /**
     * Reads the contents of a file. If the file does not exist, returns an empty string.
     * 
     * @param string $file_name The name of the file, with extentions.
     *
     * @return string
     */
    public function getFileContents(string $file_name): string;

    /**
     * Writes the given content to a file. If the file does not exist, it will be created automatically.
     * 
     * @param string $file_name The name of the file, with extentions.
     * @param string $file_contents The content to write to the file.
     * @param bool $append_mode Change the behavior to append content to the file instead of overwriting
     *
     * @return void
     */
    public function putFileContents(string $file_name, string $file_contents, bool $append_mode = false): void;

}
